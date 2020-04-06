<?php

namespace common\components;

use Yii;
use yii\helpers\Json;
use yii\redis\Connection;

/**
 * RedisConnection class
 *
 * Generate session redis list for user, add events to list, get events from list, destroy list
 */
class RedisConnection extends Connection
{

    /** @var string Redis publish main channel */
    const CHANNEL = 'main_channel';
    /** @var boolean Need user redis transactions */
    public $useTransaction = true;
    /** @var integer Count events in queue */
    private $countEvents = 0;
    /** @var boolean Transaction started */
    private $transaction = false;

    /**
     * Get count events in queue
     *
     * @return int Count events
     */
    public function getCountEvents()
    {
        return $this->countEvents;
    }

    /**
     * Add event to queue
     *
     * @param string $event Event name
     * @param array|string $rooms Rooms or room where send data
     * @param array $data Data of event
     *
     * @return bool true - success, false - not failed
     */
    public function addEvent(string $event, $rooms, array $data)
    {
        $this->startTransaction();

        $params = [
            'event' => $event,
            'data' => $data,
        ];

        // one or many rooms
        if (is_array($rooms)) {
            $params['rooms'] = $rooms;
        } else {
            $params['room'] = $rooms;
        }

        $this->countEvents++;

        return $this->executeCommand('PUBLISH', [
            self::CHANNEL,
            json_encode($params),
        ]);
    }

    /**
     * Start transaction
     *
     * @return boolean true - successfully, false - not
     */
    public function startTransaction(): bool
    {
        // check transaction created
        if ($this->useTransaction && !$this->transaction) {
            $this->beforeStartTransaction();
            $this->transaction = (bool)$this->executeCommand('MULTI');

            return $this->transaction;
        } else {
            return true;
        }
    }

    /**
     * Before start transaction
     */
    public function beforeStartTransaction()
    {
    }

    /** @inheritdoc */
    public function executeCommand($name, $params = [])
    {
        switch ($name) {
            case 'GET':
                $logText = "Executing Redis Command: {$name} key \"Params:\n" . $params[0] . '"';
                break;
            case 'SELECT':
                $logText = "Executing Redis Command: {$name} DB " . $params[0];
                break;
            default:
                $logText = "Executing Redis Command: {$name}\nParams:\n" . print_r($params, true);
        }

        $result = parent::executeCommand($name, $params);

        if (YII_DEBUG && REDIS_DEBUG) {
            if ($result === null) {
                $result = 'null';
            }
            Yii::debug($logText . "\nRedis return: \"" . (is_array($result) ? print_r($result, true) : $result) . '"', __METHOD__);
        }

        return $result;
    }

    /**
     * Delete key
     *
     * @param string $key Key want to delete
     *
     * @return boolean whether queue is destroyed successfully
     */
    public function delete(string $key)
    {
        $this->startTransaction();

        return (bool)$this->executeCommand('DEL', [$key]);
    }

    /**
     * Add the specified members to the set stored at key
     *
     * @param string $key Key
     * @param array $params Array of elements
     *
     * @return boolean success
     */
    public function sadd(string $key, array $params)
    {
        $this->startTransaction();
        array_unshift($params, $key);

        return $this->executeCommand('SADD', $params);
    }

    /**
     * Add the specified value to key
     *
     * @param string $key Key
     * @param array $value Value
     *
     * @return boolean success
     */
    public function setJson(string $key, array $value)
    {
        return $this->set($key, Json::encode($value));
    }

    /**
     * Add the specified value to key
     *
     * @param string $key Key
     * @param string $value Value
     * @param int|null $expired Set the specified expire time, in seconds.
     *
     * @return boolean success
     */
    public function set(string $key, $value, int $expired = null): bool
    {
        $this->startTransaction();

        $params = [$key, $value];
        if ($expired !== null) {
            $params[] = 'EX';
            $params[] = $expired;
        }

        return $this->executeCommand('SET', $params);
    }

    /**
     * Get the specified value to key
     *
     * @param string $key Key
     *
     * @return array|null
     */
    public function getJson(string $key): ?array
    {
        return Json::decode($this->get($key));
    }

    /**
     * Get the specified value to key
     *
     * @param string $key Key
     *
     * @return string|int|null
     */
    public function get(string $key)
    {
        $value = $this->executeCommand('GET', [$key]);
        if ($value === "null") {
            $value = null;
        }

        return $value;
    }

    /**
     * Commit transaction
     *
     * @return boolean true - successfull, false - not
     */
    public function commitTransaction(): bool
    {
        // check transaction created
        if ($this->transaction) {
            $result = (bool)$this->executeCommand('EXEC');
            $this->transaction = $result ? false : true;

            return $result;
        } else {
            return true;
        }
    }

    /**
     * Abort a transaction
     *
     * @return boolean true - successfull, false - not
     */
    public function discardTransaction(): bool
    {
        // check transaction created
        if ($this->transaction) {
            $result = (bool)$this->executeCommand('DISCARD');
            $this->transaction = $result ? false : true;

            return $result;
        } else {
            return true;
        }
    }

    /**
     * Subscribe to channel
     *
     * @param string $channelName Channel name
     *
     * @return array|bool|string
     */
    public function subscribe(string $channelName)
    {
        return $this->executeCommand('SUBSCRIBE', $channelName);
    }

    /**
     * Append one or multiple values to a list
     *
     * @param string $key
     * @param mixed $value
     *
     * @return array|bool|null|string
     */
    public function rpush($key, $value)
    {
        return $this->executeCommand('RPUSH', [$key, $value]);
    }
}
