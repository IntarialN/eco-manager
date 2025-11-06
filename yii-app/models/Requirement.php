<?php
namespace app\models;

use yii\db\ActiveRecord;

class Requirement extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%requirement}}';
    }

    public function rules(): array
    {
        return [
            [['client_id', 'code', 'title'], 'required'],
            [['client_id', 'site_id'], 'integer'],
            [['due_date', 'completed_at'], 'safe'],
            [['status', 'category'], 'string', 'max' => 50],
            [['code'], 'string', 'max' => 64],
            [['title'], 'string', 'max' => 255],
            [['client_id'], 'exist', 'targetClass' => Client::class, 'targetAttribute' => ['client_id' => 'id']],
        ];
    }

    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    public function getSite()
    {
        return $this->hasOne(Site::class, ['id' => 'site_id']);
    }

    public function getDocuments()
    {
        return $this->hasMany(Document::class, ['requirement_id' => 'id']);
    }
}
