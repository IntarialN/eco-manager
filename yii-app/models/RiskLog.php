<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $risk_id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $notes
 * @property string|null $created_at
 *
 * @property Risk $risk
 * @property User|null $user
 */
class RiskLog extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%risk_log}}';
    }

    public function rules(): array
    {
        return [
            [['risk_id', 'action'], 'required'],
            [['risk_id', 'user_id'], 'integer'],
            [['notes'], 'string'],
            [['created_at'], 'safe'],
            [['action'], 'string', 'max' => 100],
            [
                ['risk_id'],
                'exist',
                'targetClass' => Risk::class,
                'targetAttribute' => ['risk_id' => 'id'],
                'skipOnError' => true,
            ],
            [
                ['user_id'],
                'exist',
                'targetClass' => User::class,
                'targetAttribute' => ['id' => 'user_id'],
                'skipOnError' => true,
            ],
        ];
    }

    public function getRisk()
    {
        return $this->hasOne(Risk::class, ['id' => 'risk_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
