<?php

use yii\db\Migration;

class m230000_000012_extend_client_profile extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%client}}', 'annual_emissions_tons', $this->decimal(10, 2)->notNull()->defaultValue(0));
        $this->addColumn('{{%client}}', 'annual_waste_kg', $this->decimal(12, 2)->notNull()->defaultValue(0));
        $this->addColumn('{{%client}}', 'hazardous_waste_present', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%client}}', 'hazardous_substances_class', $this->string(10));
        $this->addColumn('{{%client}}', 'has_well', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%client}}', 'uses_surface_water', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%client}}', 'livestock_byproducts', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%client}}', 'responsible_person_trained', $this->boolean()->notNull()->defaultValue(true));
        $this->addColumn('{{%client}}', 'responsible_person_count', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('{{%client}}', 'instruction_docs_required', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%client}}', 'water_source', $this->string(50));
        $this->addColumn('{{%client}}', 'training_valid_until', $this->date());

        $this->update('{{%client}}', [
            'category' => 'III',
        ], [
            'category' => 'III категория НВОС',
        ]);
    }

    public function safeDown()
    {
        $this->dropColumn('{{%client}}', 'training_valid_until');
        $this->dropColumn('{{%client}}', 'water_source');
        $this->dropColumn('{{%client}}', 'instruction_docs_required');
        $this->dropColumn('{{%client}}', 'responsible_person_count');
        $this->dropColumn('{{%client}}', 'responsible_person_trained');
        $this->dropColumn('{{%client}}', 'livestock_byproducts');
        $this->dropColumn('{{%client}}', 'uses_surface_water');
        $this->dropColumn('{{%client}}', 'has_well');
        $this->dropColumn('{{%client}}', 'hazardous_substances_class');
        $this->dropColumn('{{%client}}', 'hazardous_waste_present');
        $this->dropColumn('{{%client}}', 'annual_waste_kg');
        $this->dropColumn('{{%client}}', 'annual_emissions_tons');
    }
}
