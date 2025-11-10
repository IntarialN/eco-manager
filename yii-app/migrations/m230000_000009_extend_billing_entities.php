<?php
use yii\db\Migration;

class m230000_000009_extend_billing_entities extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%contract}}', 'client_external_id', $this->string(64)->defaultValue(null));
        $this->addColumn('{{%contract}}', 'integration_id', $this->string(64));
        $this->addColumn('{{%contract}}', 'integration_revision', $this->string(64)->defaultValue(null));
        $this->addColumn('{{%contract}}', 'currency', $this->string(8)->defaultValue('RUB'));
        $this->addColumn('{{%contract}}', 'valid_from', $this->date()->defaultValue(null));
        $this->createIndex('idx-contract-integration_id', '{{%contract}}', 'integration_id', true);

        $this->addColumn('{{%invoice}}', 'integration_id', $this->string(64));
        $this->addColumn('{{%invoice}}', 'due_date', $this->date()->defaultValue(null));
        $this->addColumn('{{%invoice}}', 'currency', $this->string(8)->defaultValue('RUB'));
        $this->createIndex('idx-invoice-integration_id', '{{%invoice}}', 'integration_id', true);

        $this->addColumn('{{%act}}', 'integration_id', $this->string(64));
        $this->addColumn('{{%act}}', 'invoice_id', $this->integer()->defaultValue(null));
        $this->addColumn('{{%act}}', 'integration_revision', $this->string(64)->defaultValue(null));
        $this->createIndex('idx-act-integration_id', '{{%act}}', 'integration_id', true);

        if ($this->db->driverName !== 'sqlite') {
            $this->addForeignKey('fk_act_invoice', '{{%act}}', 'invoice_id', '{{%invoice}}', 'id', 'SET NULL', 'CASCADE');
        }
    }

    public function safeDown()
    {
        if ($this->db->driverName !== 'sqlite') {
            $this->dropForeignKey('fk_act_invoice', '{{%act}}');
        }

        $this->dropIndex('idx-act-integration_id', '{{%act}}');
        $this->dropColumn('{{%act}}', 'integration_revision');
        $this->dropColumn('{{%act}}', 'invoice_id');
        $this->dropColumn('{{%act}}', 'integration_id');

        $this->dropIndex('idx-invoice-integration_id', '{{%invoice}}');
        $this->dropColumn('{{%invoice}}', 'currency');
        $this->dropColumn('{{%invoice}}', 'due_date');
        $this->dropColumn('{{%invoice}}', 'integration_id');

        $this->dropIndex('idx-contract-integration_id', '{{%contract}}');
        $this->dropColumn('{{%contract}}', 'valid_from');
        $this->dropColumn('{{%contract}}', 'currency');
        $this->dropColumn('{{%contract}}', 'integration_revision');
        $this->dropColumn('{{%contract}}', 'integration_id');
        $this->dropColumn('{{%contract}}', 'client_external_id');
    }
}
