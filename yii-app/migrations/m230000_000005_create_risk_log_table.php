<?php

use yii\db\Migration;

/**
 * Handles the creation of table `risk_log`.
 */
class m230000_000005_create_risk_log_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%risk_log}}', [
            'id' => $this->primaryKey(),
            'risk_id' => $this->integer()->notNull(),
            'user_id' => $this->integer(),
            'action' => $this->string(100)->notNull(),
            'notes' => $this->text(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-risk_log-risk_id', '{{%risk_log}}', 'risk_id');
        $this->createIndex('idx-risk_log-user_id', '{{%risk_log}}', 'user_id');

        if ($this->db->driverName !== 'sqlite') {
            $this->addForeignKey(
                'fk-risk_log-risk_id',
                '{{%risk_log}}',
                'risk_id',
                '{{%risk}}',
                'id',
                'CASCADE',
                'CASCADE'
            );

            $this->addForeignKey(
                'fk-risk_log-user_id',
                '{{%risk_log}}',
                'user_id',
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
            $this->dropForeignKey('fk-risk_log-user_id', '{{%risk_log}}');
            $this->dropForeignKey('fk-risk_log-risk_id', '{{%risk_log}}');
        }

        $this->dropTable('{{%risk_log}}');
    }
}
