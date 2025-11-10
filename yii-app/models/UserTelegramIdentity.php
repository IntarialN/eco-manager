<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $user_id
 * @property string $telegram_user_id
 * @property string|null $telegram_username
 * @property string|null $confirmed_at
 * @property string $created_at
 *
 * @property User $user
 */
class UserTelegramIdentity extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%user_telegram_identity}}';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'telegram_user_id'], 'required'],
            [['user_id'], 'integer'],
            [['confirmed_at', 'created_at'], 'safe'],
            [['telegram_user_id', 'telegram_username'], 'string', 'max' => 64],
            [['user_id'], 'unique'],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
