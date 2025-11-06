<?php

use yii\db\Migration;

class m230000_000002_create_user_client_assignment_table extends Migration
{
    public function safeUp()
    {
        $isSqlite = $this->db->driverName === 'sqlite';
        $supportsForeignKeys = !$isSqlite;

        $this->createTable('{{%user_client_assignment}}', [
            'user_id' => $this->integer()->notNull(),
            'client_id' => $this->integer()->notNull(),
        ]);

        if (!$isSqlite) {
            $this->addPrimaryKey('pk_user_client_assignment', '{{%user_client_assignment}}', ['user_id', 'client_id']);
        } else {
            $this->createIndex(
                'idx_user_client_assignment_unique',
                '{{%user_client_assignment}}',
                ['user_id', 'client_id'],
                true
            );
        }

        if ($supportsForeignKeys) {
            $this->addForeignKey(
                'fk_user_client_assignment_user',
                '{{%user_client_assignment}}',
                'user_id',
                '{{%user}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
            $this->addForeignKey(
                'fk_user_client_assignment_client',
                '{{%user_client_assignment}}',
                'client_id',
                '{{%client}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }

        $managerId = $this->findUserIdByUsername('manager');
        $clientId = $this->findClientIdByRegistrationNumber('7701234567');

        if ($managerId && $clientId) {
            $this->insert('{{%user_client_assignment}}', [
                'user_id' => $managerId,
                'client_id' => $clientId,
            ]);
        }
    }

    public function safeDown()
    {
        $isSqlite = $this->db->driverName === 'sqlite';
        $supportsForeignKeys = !$isSqlite;

        if ($isSqlite) {
            $this->dropIndex('idx_user_client_assignment_unique', '{{%user_client_assignment}}');
        } else {
            $this->dropPrimaryKey('pk_user_client_assignment', '{{%user_client_assignment}}');
        }

        if ($supportsForeignKeys) {
            $this->dropForeignKey('fk_user_client_assignment_user', '{{%user_client_assignment}}');
            $this->dropForeignKey('fk_user_client_assignment_client', '{{%user_client_assignment}}');
        }

        $this->dropTable('{{%user_client_assignment}}');
    }

    private function findUserIdByUsername(string $username): ?int
    {
        $id = (new \yii\db\Query())
            ->select('id')
            ->from('{{%user}}')
            ->where(['username' => $username])
            ->scalar();

        return $id ? (int)$id : null;
    }

    private function findClientIdByRegistrationNumber(string $registrationNumber): ?int
    {
        $id = (new \yii\db\Query())
            ->select('id')
            ->from('{{%client}}')
            ->where(['registration_number' => $registrationNumber])
            ->scalar();

        return $id ? (int)$id : null;
    }
}
