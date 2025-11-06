<?php
namespace app\models;

use yii\db\ActiveRecord;

class Risk extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%risk}}';
    }

    public function rules(): array
    {
        return [
            [['client_id', 'title', 'severity'], 'required'],
            [['client_id', 'requirement_id'], 'integer'],
            [['detected_at', 'resolved_at'], 'safe'],
            [['loss_min', 'loss_max'], 'number'],
            [['severity', 'status'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
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
