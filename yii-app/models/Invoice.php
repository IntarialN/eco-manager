<?php
namespace app\models;

use yii\db\ActiveRecord;

class Invoice extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%invoice}}';
    }

    public function rules(): array
    {
        return [
            [['contract_id', 'number', 'status', 'amount'], 'required'],
            [['contract_id'], 'integer'],
            [['issued_at', 'paid_at'], 'safe'],
            [['amount'], 'number'],
            [['status'], 'string', 'max' => 50],
            [['number'], 'string', 'max' => 100],
            [['contract_id'], 'exist', 'targetClass' => Contract::class, 'targetAttribute' => ['contract_id' => 'id']],
        ];
    }

    public function getContract()
    {
        return $this->hasOne(Contract::class, ['id' => 'contract_id']);
    }
}
