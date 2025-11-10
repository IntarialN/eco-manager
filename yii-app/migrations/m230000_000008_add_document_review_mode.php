<?php

use yii\db\Migration;

class m230000_000008_add_document_review_mode extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%document}}', 'review_mode', $this->string(20)->notNull()->defaultValue('storage'));
        $this->addColumn('{{%document}}', 'auditor_id', $this->integer()->null());
        $this->addColumn('{{%document}}', 'audit_comment', $this->text()->null());
        $this->addColumn('{{%document}}', 'audit_completed_at', $this->dateTime()->null());

        $this->createIndex('idx-document-auditor_id', '{{%document}}', 'auditor_id');
 
        if ($this->db->driverName !== 'sqlite') {
            $this->addForeignKey(
                'fk-document-auditor',
                '{{%document}}',
                'auditor_id',
                '{{%user}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
        }
    }

    public function safeDown()
    {
        if ($this->db->driverName !== 'sqlite') {
            $this->dropForeignKey('fk-document-auditor', '{{%document}}');
        }
        $this->dropIndex('idx-document-auditor_id', '{{%document}}');
        $this->dropColumn('{{%document}}', 'audit_completed_at');
        $this->dropColumn('{{%document}}', 'audit_comment');
        $this->dropColumn('{{%document}}', 'auditor_id');
        $this->dropColumn('{{%document}}', 'review_mode');
    }
}
