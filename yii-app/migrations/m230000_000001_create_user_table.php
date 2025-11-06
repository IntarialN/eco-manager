<?php

use yii\db\Migration;

class m230000_000001_create_user_table extends Migration
{
    public function safeUp()
    {
        $supportsForeignKeys = $this->db->driverName !== 'sqlite';

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'client_id' => $this->integer(),
            'username' => $this->string()->notNull()->unique(),
            'email' => $this->string()->notNull()->unique(),
            'role' => $this->string(64)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'access_token' => $this->string(64),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'last_login_at' => $this->dateTime(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        if ($supportsForeignKeys) {
            $this->addForeignKey(
                'fk_user_client',
                '{{%user}}',
                'client_id',
                '{{%client}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
        }

        $now = time();
        $security = \Yii::$app->security;

        $users = [
            [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'password' => 'Admin#2025',
                'client_id' => null,
            ],
            [
                'username' => 'manager',
                'email' => 'manager@example.com',
                'role' => 'client_manager',
                'password' => 'Manager#2025',
                'client_id' => 1,
            ],
            [
                'username' => 'client',
                'email' => 'client@example.com',
                'role' => 'client_user',
                'password' => 'Client#2025',
                'client_id' => 1,
            ],
        ];

        foreach ($users as $user) {
            $this->insert('{{%user}}', [
                'client_id' => $user['client_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'password_hash' => $security->generatePasswordHash($user['password']),
                'auth_key' => $security->generateRandomString(),
                'access_token' => null,
                'is_active' => true,
                'last_login_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function safeDown()
    {
        $supportsForeignKeys = $this->db->driverName !== 'sqlite';

        if ($supportsForeignKeys) {
            $this->dropForeignKey('fk_user_client', '{{%user}}');
        }

        $this->dropTable('{{%user}}');
    }
}
