<?php
namespace app\models;

use yii\db\ActiveRecord;

class CalendarEvent extends ActiveRecord
{
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_DONE = 'done';
    public const STATUS_OVERDUE = 'overdue';

    public static function tableName(): string
    {
        return '{{%calendar_event}}';
    }

    public function rules(): array
    {
        return [
            [['client_id', 'title', 'due_date'], 'required'],
            [['client_id', 'requirement_id'], 'integer'],
            [['due_date', 'completed_at', 'created_at'], 'safe'],
            [['status', 'type'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 255],
            [['client_id'], 'exist', 'targetClass' => Client::class, 'targetAttribute' => ['client_id' => 'id']],
        ];
    }

    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    public function getRequirement()
    {
        return $this->hasOne(Requirement::class, ['id' => 'requirement_id']);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Запланировано',
            self::STATUS_DONE => 'Выполнено',
            self::STATUS_OVERDUE => 'Просрочено',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}
