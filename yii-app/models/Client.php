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
