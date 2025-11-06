<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $user_id
 * @property int $client_id
 *
 * @property User $user
 * @property Client $client
 */
class UserClientAssignment extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%user_client_assignment}}';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'client_id'], 'required'],
            [['user_id', 'client_id'], 'integer'],
            [['user_id', 'client_id'], 'unique', 'targetAttribute' => ['user_id', 'client_id']],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }
}
