<?php

use yii\db\Migration;
use yii\db\Query;

class m230000_000007_seed_additional_clients extends Migration
{
    private array $registrations = [
        '7812345678',
        '5409876543',
        '2311223344',
    ];

    public function safeUp()
    {
        $now = time();

        $this->batchInsert('{{%client}}', ['name', 'registration_number', 'category', 'description', 'created_at', 'updated_at'], [
            [
                'ООО "Зеленый Паттерн Север"',
                $this->registrations[0],
                'II категория НВОС',
                'Региональный офис в Северо-Западном федеральном округе с портфелем проектов по отходам.',
                $now,
                $now,
            ],
            [
                'АО "Чистый Город"',
                $this->registrations[1],
                'IV категория НВОС',
                'Муниципальный подрядчик по содержанию водоочистных сооружений и мониторингу выбросов.',
                $now,
                $now,
            ],
            [
                'ООО "Эко Логистик"',
                $this->registrations[2],
                'III категория НВОС',
                'Логистическая компания, работающая с перевозкой опасных отходов в Южном регионе.',
                $now,
                $now,
            ],
        ]);

        $clients = (new Query())
            ->select(['id', 'registration_number'])
            ->from('{{%client}}')
            ->where(['registration_number' => $this->registrations])
            ->indexBy('registration_number')
            ->column();

        $managerId = (new Query())
            ->select('id')
            ->from('{{%user}}')
            ->where(['username' => 'manager'])
            ->scalar();

        if ($managerId) {
            foreach ($clients as $clientId) {
                $this->insert('{{%user_client_assignment}}', [
                    'user_id' => (int)$managerId,
                    'client_id' => (int)$clientId,
                ]);
            }
        }
    }

    public function safeDown()
    {
        $clients = (new Query())
            ->select('id')
            ->from('{{%client}}')
            ->where(['registration_number' => $this->registrations])
            ->column();

        if ($clients) {
            $this->delete('{{%user_client_assignment}}', [
                'client_id' => $clients,
            ]);
        }

        $this->delete('{{%client}}', ['registration_number' => $this->registrations]);
    }
}
