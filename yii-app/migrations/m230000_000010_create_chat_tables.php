<?php

use yii\db\Migration;

class m230000_000010_create_chat_tables extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%chat_session}}', [
            'id' => $this->primaryKey(),
            'client_id' => $this->integer()->defaultValue(null),
            'external_contact' => $this->string(128)->defaultValue(null),
            'name' => $this->string(128)->defaultValue(null),
            'source' => $this->string(32)->notNull(),
            'initiator' => $this->string(32)->notNull()->defaultValue('client'),
            'status' => $this->string(32)->notNull()->defaultValue('open'),
            'assigned_user_id' => $this->integer()->defaultValue(null),
            'last_message_at' => $this->dateTime()->defaultValue(null),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
        $this->createIndex('idx-chat_session-client_id', '{{%chat_session}}', 'client_id');
        $this->createIndex('idx-chat_session-status', '{{%chat_session}}', 'status');

        $this->createTable('{{%chat_message}}', [
            'id' => $this->primaryKey(),
            'session_id' => $this->integer()->notNull(),
            'sender_type' => $this->string(32)->notNull(),
            'sender_id' => $this->integer()->defaultValue(null),
            'direction' => $this->string(32)->notNull()->defaultValue('web_to_bot'),
            'body' => $this->text()->notNull(),
            'attachments' => $this->text()->defaultValue(null),
            'delivered_at' => $this->dateTime()->defaultValue(null),
            'read_at' => $this->dateTime()->defaultValue(null),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
        $this->createIndex('idx-chat_message-session_id', '{{%chat_message}}', 'session_id');
        $this->createIndex('idx-chat_message-sender_type', '{{%chat_message}}', 'sender_type');

        $this->createTable('{{%callback_request}}', [
            'id' => $this->primaryKey(),
            'session_id' => $this->integer()->notNull(),
            'phone' => $this->string(32)->notNull(),
            'preferred_time' => $this->dateTime()->defaultValue(null),
            'status' => $this->string(32)->notNull()->defaultValue('pending'),
            'comment' => $this->text()->defaultValue(null),
            'processed_at' => $this->dateTime()->defaultValue(null),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
        $this->createIndex('idx-callback_request-session_id', '{{%callback_request}}', 'session_id');
        $this->createIndex('idx-callback_request-status', '{{%callback_request}}', 'status');

        $this->createTable('{{%user_telegram_identity}}', [
            'user_id' => $this->integer()->notNull(),
            'telegram_user_id' => $this->string(64)->notNull(),
            'telegram_username' => $this->string(64)->defaultValue(null),
            'confirmed_at' => $this->dateTime()->defaultValue(null),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'PRIMARY KEY(user_id)',
        ]);
        $this->createIndex('idx-user_telegram_identity-telegram_user_id', '{{%user_telegram_identity}}', 'telegram_user_id', true);

        if ($this->db->driverName !== 'sqlite') {
            $this->addForeignKey(
                'fk_chat_session_client',
                '{{%chat_session}}',
                'client_id',
                '{{%client}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
            $this->addForeignKey(
                'fk_chat_session_assigned_user',
                '{{%chat_session}}',
                'assigned_user_id',
                '{{%user}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
            $this->addForeignKey(
                'fk_chat_message_session',
                '{{%chat_message}}',
                'session_id',
                '{{%chat_session}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
            $this->addForeignKey(
                'fk_callback_request_session',
                '{{%callback_request}}',
                'session_id',
                '{{%chat_session}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
            $this->addForeignKey(
                'fk_user_telegram_identity_user',
                '{{%user_telegram_identity}}',
                'user_id',
                '{{%user}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }
    }

    public function safeDown()
    {
        if ($this->db->driverName !== 'sqlite') {
            $this->dropForeignKey('fk_user_telegram_identity_user', '{{%user_telegram_identity}}');
            $this->dropForeignKey('fk_callback_request_session', '{{%callback_request}}');
            $this->dropForeignKey('fk_chat_message_session', '{{%chat_message}}');
            $this->dropForeignKey('fk_chat_session_assigned_user', '{{%chat_session}}');
            $this->dropForeignKey('fk_chat_session_client', '{{%chat_session}}');
        }

        $this->dropTable('{{%user_telegram_identity}}');
        $this->dropTable('{{%callback_request}}');
        $this->dropTable('{{%chat_message}}');
        $this->dropTable('{{%chat_session}}');
    }
}
