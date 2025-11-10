<?php

use yii\db\Migration;

class m230000_000011_add_chat_seen_fields extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%chat_session}}', 'assigned_seen_at', $this->dateTime()->defaultValue(null));
        $this->createIndex('idx-chat_session-assigned_seen', '{{%chat_session}}', 'assigned_seen_at');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-chat_session-assigned_seen', '{{%chat_session}}');
        $this->dropColumn('{{%chat_session}}', 'assigned_seen_at');
    }
}
