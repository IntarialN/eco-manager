<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $risk_id
 * @property string $task
 * @property int|null $owner_id
 * @property string $status
 * @property string|null $due_date
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Risk $risk
 * @property User|null $owner
 */
class RiskActionPlan extends ActiveRecord
{
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    public static function tableName(): string
    {
        return '{{%risk_action_plan}}';
    }

    public function rules(): array
    {
        return [
            [['risk_id', 'task'], 'required'],
            [['risk_id', 'owner_id'], 'integer'],
            [['task'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 32],
            [['due_date', 'created_at', 'updated_at'], 'safe'],
            [
                'status',
                'in',
                'range' => [
                    self::STATUS_NEW,
                    self::STATUS_IN_PROGRESS,
                    self::STATUS_DONE,
                ],
            ],
            [
                ['risk_id'],
                'exist',
                'targetClass' => Risk::class,
                'targetAttribute' => ['risk_id' => 'id'],
                'skipOnError' => true,
            ],
            [
                ['owner_id'],
                'exist',
                'targetClass' => User::class,
                'targetAttribute' => ['owner_id' => 'id'],
                'skipOnError' => true,
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'task' => 'Задача',
            'owner_id' => 'Ответственный',
            'status' => 'Статус',
            'due_date' => 'Срок',
        ];
    }

    public function getRisk()
    {
        return $this->hasOne(Risk::class, ['id' => 'risk_id']);
    }

    public function getOwner()
    {
        return $this->hasOne(User::class, ['id' => 'owner_id']);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEW => 'Не начато',
            self::STATUS_IN_PROGRESS => 'В работе',
            self::STATUS_DONE => 'Выполнено',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}
