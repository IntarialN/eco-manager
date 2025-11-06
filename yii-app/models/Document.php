<?php
namespace app\models;

use yii\db\ActiveRecord;

class Document extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%document}}';
    }

    public function rules(): array
    {
        return [
            [['client_id', 'title', 'type'], 'required'],
            [['client_id', 'requirement_id'], 'integer'],
            [['uploaded_at'], 'safe'],
            [['path'], 'string'],
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
