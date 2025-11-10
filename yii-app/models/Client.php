<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Client extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%client}}';
    }

    public function behaviors(): array
    {
        return [TimestampBehavior::class];
    }

    public function rules(): array
    {
        return [
            [['name', 'registration_number', 'category'], 'required'],
            [['description'], 'string'],
            [['annual_emissions_tons', 'annual_waste_kg'], 'number'],
            [['responsible_person_count'], 'integer'],
            [
                [
                    'hazardous_waste_present',
                    'has_well',
                    'uses_surface_water',
                    'livestock_byproducts',
                    'responsible_person_trained',
                    'instruction_docs_required',
                ],
                'boolean',
            ],
            [['training_valid_until'], 'date', 'format' => 'php:Y-m-d'],
            [['hazardous_substances_class', 'water_source'], 'string', 'max' => 50],
            [['created_at', 'updated_at'], 'integer'],
            [['name', 'registration_number', 'category'], 'string', 'max' => 255],
            [['registration_number'], 'unique'],
        ];
    }

    public function getSites()
    {
        return $this->hasMany(Site::class, ['client_id' => 'id']);
    }

    public function getRequirements()
    {
        return $this->hasMany(Requirement::class, ['client_id' => 'id']);
    }

    public function getDocuments()
    {
        return $this->hasMany(Document::class, ['client_id' => 'id']);
    }

    public function getCalendarEvents()
    {
        return $this->hasMany(CalendarEvent::class, ['client_id' => 'id']);
    }

    public function getRisks()
    {
        return $this->hasMany(Risk::class, ['client_id' => 'id']);
    }

    public function getContracts()
    {
        return $this->hasMany(Contract::class, ['client_id' => 'id']);
    }
}
