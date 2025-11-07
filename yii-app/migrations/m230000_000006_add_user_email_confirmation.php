<?php

use yii\db\Migration;

class m230000_000006_add_user_email_confirmation extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'email_confirm_token', $this->string(64)->null());
        $this->addColumn('{{%user}}', 'email_confirmed_at', $this->dateTime()->null());

        $this->createIndex('idx-user-email_confirm_token', '{{%user}}', 'email_confirm_token', true);
    }

    public function safeDown()
    {
        $this->dropIndex('idx-user-email_confirm_token', '{{%user}}');
        $this->dropColumn('{{%user}}', 'email_confirmed_at');
        $this->dropColumn('{{%user}}', 'email_confirm_token');
    }
}
