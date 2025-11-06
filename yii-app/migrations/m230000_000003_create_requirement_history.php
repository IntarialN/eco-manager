<?php

use yii\db\Migration;

class m230000_000003_create_requirement_history extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%requirement_history}}', [
            'id' => $this->primaryKey(),
            'requirement_id' => $this->integer()->notNull(),
            'user_id' => $this->integer(),
            'old_status' => $this->string(50),
            'new_status' => $this->string(50)->notNull(),
            'comment' => $this->text(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $supportsForeignKeys = $this->db->driverName !== 'sqlite';

        if ($supportsForeignKeys) {
            $this->addForeignKey(
                'fk_requirement_history_requirement',
                '{{%requirement_history}}',
                'requirement_id',
                '{{%requirement}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
            $this->addForeignKey(
                'fk_requirement_history_user',
                '{{%requirement_history}}',
                'user_id',
                '{{%user}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
        } else {
            $this->createIndex('idx_requirement_history_requirement', '{{%requirement_history}}', 'requirement_id');
            $this->createIndex('idx_requirement_history_user', '{{%requirement_history}}', 'user_id');
        }
    }

    public function safeDown()
    {
        $supportsForeignKeys = $this->db->driverName !== 'sqlite';

        if ($supportsForeignKeys) {
            $this->dropForeignKey('fk_requirement_history_requirement', '{{%requirement_history}}');
            $this->dropForeignKey('fk_requirement_history_user', '{{%requirement_history}}');
        } else {
            $this->dropIndex('idx_requirement_history_requirement', '{{%requirement_history}}');
            $this->dropIndex('idx_requirement_history_user', '{{%requirement_history}}');
        }

        $this->dropTable('{{%requirement_history}}');
    }
}
