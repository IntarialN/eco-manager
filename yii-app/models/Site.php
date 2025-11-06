<?php
namespace app\models;

use yii\db\ActiveRecord;

class Site extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%site}}';
    }

    public function rules(): array
    {
        return [
            [['client_id', 'name'], 'required'],
            [['client_id'], 'integer'],
            [['address'], 'string'],
            [['emission_category'], 'string', 'max' => 10],
            [['name'], 'string', 'max' => 255],
            [['client_id'], 'exist', 'targetClass' => Client::class, 'targetAttribute' => ['client_id' => 'id']],
        ];
    }

    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }
}
