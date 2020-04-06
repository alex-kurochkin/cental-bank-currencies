<?php

use yii\db\Migration;

/**
 * Class m200406_124117_add_currency_table
 */
class m200406_124117_add_currency_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
            '{{%currency}}',
            [
                'id' => $this->primaryKey(),
                'valuteID' => $this->char(6)->notNull(),
                'numCode' => $this->smallInteger()->notNull(),
                'charCode' => $this->char(3)->notNull(),
                'name' => $this->string(128)->notNull(),
                'nominal' => $this->integer()->notNull(),
                'value' => $this->decimal()->notNull(),
                'date' => $this->date()->notNull(),
            ],
            'ENGINE=InnoDB CHARSET=utf8'
        );

        $this->createIndex('u_numCode_date', 'currency', ['numCode', 'date'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('u_numCode_date', 'currency');
        $this->dropTable('currency');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200406_124117_add_currency_table cannot be reverted.\n";

        return false;
    }
    */
}
