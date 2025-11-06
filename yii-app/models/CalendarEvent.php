<?php
namespace app\models;

use yii\db\ActiveRecord;

class CalendarEvent extends ActiveRecord
{
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
}
