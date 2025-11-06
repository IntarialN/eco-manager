<?php
use yii\db\Migration;

class m230000_000000_init_schema extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%client}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'registration_number' => $this->string()->notNull()->unique(),
            'category' => $this->string()->notNull(),
            'description' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createTable('{{%site}}', [
            'id' => $this->primaryKey(),
            'client_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'address' => $this->text(),
            'emission_category' => $this->string(10),
        ]);
        $this->addForeignKey('fk_site_client', '{{%site}}', 'client_id', '{{%client}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%requirement}}', [
            'id' => $this->primaryKey(),
            'client_id' => $this->integer()->notNull(),
            'site_id' => $this->integer(),
            'code' => $this->string(64)->notNull(),
            'title' => $this->string()->notNull(),
            'category' => $this->string(50),
            'status' => $this->string(50)->notNull(),
            'due_date' => $this->date(),
            'completed_at' => $this->date(),
        ]);
        $this->addForeignKey('fk_requirement_client', '{{%requirement}}', 'client_id', '{{%client}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_requirement_site', '{{%requirement}}', 'site_id', '{{%site}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%document}}', [
            'id' => $this->primaryKey(),
            'client_id' => $this->integer()->notNull(),
            'requirement_id' => $this->integer(),
            'title' => $this->string()->notNull(),
            'type' => $this->string(50)->notNull(),
            'status' => $this->string(50)->notNull(),
            'path' => $this->text(),
            'uploaded_at' => $this->dateTime(),
        ]);
        $this->addForeignKey('fk_document_client', '{{%document}}', 'client_id', '{{%client}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_document_requirement', '{{%document}}', 'requirement_id', '{{%requirement}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%calendar_event}}', [
            'id' => $this->primaryKey(),
            'client_id' => $this->integer()->notNull(),
            'requirement_id' => $this->integer(),
            'title' => $this->string()->notNull(),
            'type' => $this->string(50),
            'status' => $this->string(50)->notNull(),
            'due_date' => $this->date()->notNull(),
            'completed_at' => $this->date(),
            'created_at' => $this->dateTime(),
        ]);
        $this->addForeignKey('fk_event_client', '{{%calendar_event}}', 'client_id', '{{%client}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_event_requirement', '{{%calendar_event}}', 'requirement_id', '{{%requirement}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%risk}}', [
            'id' => $this->primaryKey(),
            'client_id' => $this->integer()->notNull(),
            'requirement_id' => $this->integer(),
            'title' => $this->string()->notNull(),
            'description' => $this->text(),
            'severity' => $this->string(50)->notNull(),
            'status' => $this->string(50)->notNull(),
            'loss_min' => $this->decimal(12, 2),
            'loss_max' => $this->decimal(12, 2),
            'detected_at' => $this->date(),
            'resolved_at' => $this->date(),
        ]);
        $this->addForeignKey('fk_risk_client', '{{%risk}}', 'client_id', '{{%client}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_risk_requirement', '{{%risk}}', 'requirement_id', '{{%requirement}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%contract}}', [
            'id' => $this->primaryKey(),
            'client_id' => $this->integer()->notNull(),
            'number' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'status' => $this->string(50)->notNull(),
            'amount' => $this->decimal(12,2)->notNull()->defaultValue(0),
            'signed_at' => $this->date(),
            'valid_until' => $this->date(),
        ]);
        $this->addForeignKey('fk_contract_client', '{{%contract}}', 'client_id', '{{%client}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%invoice}}', [
            'id' => $this->primaryKey(),
            'contract_id' => $this->integer()->notNull(),
            'number' => $this->string()->notNull(),
            'status' => $this->string(50)->notNull(),
            'amount' => $this->decimal(12,2)->notNull()->defaultValue(0),
            'issued_at' => $this->date(),
            'paid_at' => $this->date(),
        ]);
        $this->addForeignKey('fk_invoice_contract', '{{%invoice}}', 'contract_id', '{{%contract}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%act}}', [
            'id' => $this->primaryKey(),
            'contract_id' => $this->integer()->notNull(),
            'number' => $this->string()->notNull(),
            'status' => $this->string(50)->notNull(),
            'issued_at' => $this->date(),
        ]);
        $this->addForeignKey('fk_act_contract', '{{%act}}', 'contract_id', '{{%contract}}', 'id', 'CASCADE', 'CASCADE');

        // Seed example data
        $now = time();
        $this->insert('{{%client}}', [
            'id' => 1,
            'name' => 'ООО "Зеленый Паттерн"',
            'registration_number' => '7701234567',
            'category' => 'III категория НВОС',
            'description' => 'Производственное предприятие с одной площадкой и собственной скважиной.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->batchInsert('{{%site}}', ['client_id', 'name', 'address', 'emission_category'], [
            [1, 'Основная площадка', 'Московская область, г. Электросталь, промзона 12', 'III'],
        ]);

        $this->batchInsert('{{%requirement}}', ['id', 'client_id', 'site_id', 'code', 'title', 'category', 'status', 'due_date', 'completed_at'], [
            [1, 1, 1, 'REQ-01', 'Журнал учета движения отходов', 'waste', 'done', '2025-01-31', '2025-02-01'],
            [2, 1, 1, 'REQ-05', 'Декларация платы за НВОС', 'payments', 'in_progress', '2025-03-20', null],
            [3, 1, 1, 'REQ-12', 'НООЛР', 'waste', 'open', '2025-05-15', null],
        ]);

        $this->batchInsert('{{%document}}', ['client_id', 'requirement_id', 'title', 'type', 'status', 'path', 'uploaded_at'], [
            [1, 1, 'Журнал учета отходов 2024', 'journal', 'approved', '/uploads/journal-2024.pdf', '2025-02-02 10:00:00'],
            [1, 2, 'Декларация НВОС (черновик)', 'declaration', 'pending_review', '/uploads/decl-2025-draft.docx', '2025-02-20 14:30:00'],
            [1, null, 'Договор аренды контейнеров', 'contract', 'approved', '/uploads/container-contract.pdf', '2025-01-12 09:00:00'],
        ]);

        $this->batchInsert('{{%calendar_event}}', ['client_id', 'requirement_id', 'title', 'type', 'status', 'due_date', 'created_at'], [
            [1, 2, 'Сдать декларацию НВОС', 'report', 'scheduled', '2025-03-20', '2025-01-15 08:00:00'],
            [1, 3, 'Обновить НООЛР', 'compliance', 'scheduled', '2025-05-15', '2025-01-15 08:05:00'],
            [1, null, 'Проверка Росприроднадзора', 'inspection', 'planned', '2025-06-01', '2025-02-10 09:00:00'],
        ]);

        $this->batchInsert('{{%risk}}', ['client_id', 'requirement_id', 'title', 'description', 'severity', 'status', 'loss_min', 'loss_max', 'detected_at'], [
            [1, 3, 'Просрочка НООЛР', 'Штраф по ст. 8.2 КоАП', 'high', 'open', 200000, 500000, '2025-02-01'],
            [1, 2, 'Несвоевременная декларация НВОС', 'Повышенный риск штрафа от 100 до 250 тыс. руб.', 'medium', 'monitoring', 100000, 250000, '2025-02-15'],
        ]);

        $this->batchInsert('{{%contract}}', ['id', 'client_id', 'number', 'title', 'status', 'amount', 'signed_at', 'valid_until'], [
            [1, 1, 'ДГ-001/25', 'Экологическое сопровождение 2025', 'active', 450000.00, '2025-01-10', '2025-12-31'],
        ]);

        $this->batchInsert('{{%invoice}}', ['contract_id', 'number', 'status', 'amount', 'issued_at', 'paid_at'], [
            [1, 'СЧ-001/25', 'paid', 225000.00, '2025-01-15', '2025-02-01'],
            [1, 'СЧ-002/25', 'issued', 225000.00, '2025-04-15', null],
        ]);

        $this->batchInsert('{{%act}}', ['contract_id', 'number', 'status', 'issued_at'], [
            [1, 'АКТ-001/25', 'signed', '2025-02-05'],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%act}}');
        $this->dropTable('{{%invoice}}');
        $this->dropTable('{{%contract}}');
        $this->dropTable('{{%risk}}');
        $this->dropTable('{{%calendar_event}}');
        $this->dropTable('{{%document}}');
        $this->dropTable('{{%requirement}}');
        $this->dropTable('{{%site}}');
        $this->dropTable('{{%client}}');
    }
}
