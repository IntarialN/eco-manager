<?php
namespace app\models;

use yii\db\ActiveRecord;

class Act extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_SIGN = 'pending_sign';
    public const STATUS_SIGNED = 'signed';
    public const STATUS_ARCHIVED = 'archived';

    public static function tableName(): string
    {
        return '{{%act}}';
    }

    public function rules(): array
    {
        return [
            [['contract_id', 'number', 'status'], 'required'],
            [['contract_id', 'invoice_id'], 'integer'],
            [['issued_at'], 'safe'],
            [['number', 'integration_id', 'integration_revision'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 50],
            [['integration_id'], 'unique'],
            [['contract_id'], 'exist', 'targetClass' => Contract::class, 'targetAttribute' => ['contract_id' => 'id']],
            [['invoice_id'], 'exist', 'targetClass' => Invoice::class, 'targetAttribute' => ['invoice_id' => 'id']],
        ];
    }

    public function getContract()
    {
        return $this->hasOne(Contract::class, ['id' => 'contract_id']);
    }

    public function getInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
    }
}
