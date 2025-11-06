<?php

use yii\db\Migration;

/**
 * Handles the creation of table `risk_action_plan`.
 */
class m230000_000004_create_risk_action_plan_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%risk_action_plan}}', [
            'id' => $this->primaryKey(),
            'risk_id' => $this->integer()->notNull(),
            'task' => $this->string(255)->notNull(),
            'owner_id' => $this->integer(),
            'status' => $this->string(32)->notNull()->defaultValue('new'),
            'due_date' => $this->date(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->null(),
        ]);

        $this->createIndex('idx-risk_action_plan-risk_id', '{{%risk_action_plan}}', 'risk_id');
        $this->createIndex('idx-risk_action_plan-owner_id', '{{%risk_action_plan}}', 'owner_id');

        if ($this->db->driverName !== 'sqlite') {
            $this->addForeignKey(
                'fk-risk_action_plan-risk_id',
                '{{%risk_action_plan}}',
                'risk_id',
                '{{%risk}}',
                'id',
                'CASCADE',
                'CASCADE'
            );

            $this->addForeignKey(
                'fk-risk_action_plan-owner_id',
                '{{%risk_action_plan}}',
                'owner_id',
                '{{%user}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
        }
    }

    public function safeDown()
    {
        if ($this->db->driverName !== 'sqlite') {
            $this->dropForeignKey('fk-risk_action_plan-owner_id', '{{%risk_action_plan}}');
            $this->dropForeignKey('fk-risk_action_plan-risk_id', '{{%risk_action_plan}}');
        }
        $this->dropTable('{{%risk_action_plan}}');
    }
}
