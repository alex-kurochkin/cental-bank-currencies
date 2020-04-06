<?php

namespace common\components\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Controller;
use yii\db\Transaction;

/**
 * Transaction Behavior automatically begins and commits transaction on any action specified in configuration.
 * To apply behavior to any action, use it in your Controller code like this:
 *
 * ```php
 *
 * public function behaviors()
 * {
 *     return [[
 *         'class'   => TransactionBehavior::class,
 *         'actions' => ['create', 'update' => Transaction::SERIALIZABLE], //where value is an optional parameter setting the isolation level
 *     ]];
 * }
 * ```
 *
 * In case of exception, transaction will be rolled back by [[common\components\ErrorHandler]]
 *
 * @see \yii\db\Transaction
 * @property \common\components\Controller $owner
 */
class TransactionBehavior extends Behavior
{

    /** @var array Actions that should use transactions */
    public $actions = [];

    /** @var boolean Whether behavior is active for the currently running action */
    private $active;

    /** @inheritdoc */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeActionHandler',
            Controller::EVENT_AFTER_ACTION => 'afterActionHandler',
        ];
    }

    /**
     * Runs before any action and begins a new transaction
     */
    public function beforeActionHandler(): void
    {
        $action = $this->owner->action;
        if (!is_array($this->actions)) {
            return;
        }

        $actions = [];
        foreach ($this->actions as $key => $value) {
            if (is_int($key)) {
                $actionName = $value;
                $isolation = Transaction::READ_UNCOMMITTED;
            } else {
                $isolation = $value;
                $actionName = $key;
            }
            $actions[$actionName] = $isolation;
        }
        if (array_key_exists($action->id, $actions) || empty($this->actions)) {
            $this->active = true;
            $db = Yii::$app->db;
            $db->createCommand('SET autocommit = 0')
                ->execute();
            $db->beginTransaction();
        }
    }

    /**
     * Runs after any action and commits the transaction
     */
    public function afterActionHandler(): void
    {
        if (!$this->active) {
            return;
        }
        $trans = Yii::$app->db->getTransaction();
        if ($trans->isActive) {
            $trans->commit();
        }
    }
}
