<?php

use yii\db\Migration;

class m230000_000013_add_calendar_recurrence extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%calendar_event}}', 'start_date', $this->date()->after('due_date'));
        $this->addColumn('{{%calendar_event}}', 'periodicity', $this->string(32)->notNull()->defaultValue('once')->after('start_date'));
        $this->addColumn('{{%calendar_event}}', 'custom_interval_days', $this->integer()->null()->after('periodicity'));
        $this->addColumn('{{%calendar_event}}', 'reminder_days', $this->string()->null()->after('custom_interval_days'));

        $this->update('{{%calendar_event}}', [
            'start_date' => new \yii\db\Expression('COALESCE(due_date, start_date)'),
        ]);
    }

    public function safeDown()
    {
        $this->dropColumn('{{%calendar_event}}', 'reminder_days');
        $this->dropColumn('{{%calendar_event}}', 'custom_interval_days');
        $this->dropColumn('{{%calendar_event}}', 'periodicity');
        $this->dropColumn('{{%calendar_event}}', 'start_date');
    }
}
