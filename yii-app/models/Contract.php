<?php
namespace app\models;

use yii\db\ActiveRecord;

class Contract extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%contract}}';
    }

    public function rules(): array
    {
        return [
            [['client_id', 'number', 'title', 'status'], 'required'],
            [['client_id'], 'integer'],
            [['signed_at', 'valid_until'], 'safe'],
            [['amount'], 'number'],
            [['status'], 'string', 'max' => 50],
            [['number', 'title'], 'string', 'max' => 255],
            [['client_id'], 'exist', 'targetClass' => Client::class, 'targetAttribute' => ['client_id' => 'id']],
        ];
    }

    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    public function getInvoices()
    {
        return $this->hasMany(Invoice::class, ['contract_id' => 'id']);
    }

    public function getActs()
    {
        return $this->hasMany(Act::class, ['contract_id' => 'id']);
    }
}
