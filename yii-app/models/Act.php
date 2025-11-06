<?php
namespace app\models;

use yii\db\ActiveRecord;

class Act extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%act}}';
    }

    public function rules(): array
    {
        return [
            [['contract_id', 'number', 'status'], 'required'],
            [['contract_id'], 'integer'],
            [['issued_at'], 'safe'],
            [['number'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 50],
            [['contract_id'], 'exist', 'targetClass' => Contract::class, 'targetAttribute' => ['contract_id' => 'id']],
        ];
    }

    public function getContract()
    {
        return $this->hasOne(Contract::class, ['id' => 'contract_id']);
    }
}
