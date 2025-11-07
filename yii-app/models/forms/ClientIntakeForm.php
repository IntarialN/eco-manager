<?php

namespace app\models\forms;

use app\models\Client;
use app\models\User;
use yii\base\Model;

class ClientIntakeForm extends Model
{
    public string $name = '';
    public string $registration_number = '';
    public string $category = '';
    public string $site_name = '';
    public string $site_address = '';
    public string $notes = '';
    public string $contact_name = '';
    public string $contact_email = '';
    public string $contact_role = '';
    public string $contact_phone = '';
    public string $access_channels = '';
    public string $okved = '';
    public string $emission_sources = '';
    public string $water_source = '';
    public string $well_license_number = '';
    public string $well_license_valid_until = '';
    public ?int $manager_id = null;
    public ?int $existingUserId = null;
    public bool $hasAirEmissions = true;
    public bool $hasWasteGeneration = true;
    public bool $hasWaterUse = false;
    public bool $hasSurfaceWaterIntake = false;
    public bool $needsInstructionDocs = false;
    public bool $needsTrainingProgram = false;

    private ?array $_managerOptions = null;
    private ?string $_selectedManagerLabel = null;

    public function rules(): array
    {
        return [
            [['name', 'registration_number', 'category', 'site_name', 'contact_email', 'contact_name'], 'required'],
            [['name', 'registration_number', 'site_name', 'contact_name', 'contact_role', 'okved'], 'string', 'max' => 255],
            [['site_address', 'notes', 'access_channels', 'emission_sources', 'water_source', 'well_license_number'], 'string'],
            [
                'category',
                'in',
                'range' => array_keys($this->getCategoryOptions()),
            ],
            [
                'registration_number',
                'unique',
                'targetClass' => Client::class,
                'targetAttribute' => ['registration_number' => 'registration_number'],
                'message' => 'Клиент с таким ИНН уже существует.',
            ],
            [
                [
                    'hasAirEmissions',
                    'hasWasteGeneration',
                    'hasWaterUse',
                    'hasSurfaceWaterIntake',
                    'needsInstructionDocs',
                    'needsTrainingProgram',
                ],
                'filter',
                'filter' => static fn($value) => filter_var($value, FILTER_VALIDATE_BOOL),
            ],
            ['contact_email', 'trim'],
            ['contact_email', 'email'],
            [
                'contact_email',
                'unique',
                'targetClass' => User::class,
                'targetAttribute' => ['contact_email' => 'email'],
                'message' => 'Пользователь с таким email уже существует.',
                'filter' => function ($query) {
                    if ($this->existingUserId) {
                        $query->andWhere(['!=', 'id', $this->existingUserId]);
                    }
                },
            ],
            ['manager_id', 'integer'],
            ['manager_id', 'validateManager'],
            ['contact_phone', 'string', 'max' => 32],
            ['contact_phone', 'match', 'pattern' => '/^[\d\+\-\s\(\)]*$/', 'message' => 'Укажите телефон в корректном формате.'],
            ['okved', 'string', 'max' => 255],
            [
                'emission_sources',
                'in',
                'range' => array_keys($this->getEmissionSourceOptions()),
                'skipOnEmpty' => true,
            ],
            [
                'water_source',
                'in',
                'range' => array_keys($this->getWaterSourceOptions()),
                'skipOnEmpty' => true,
            ],
            ['well_license_valid_until', 'date', 'format' => 'php:Y-m-d', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => 'Название компании',
            'registration_number' => 'ИНН / ОГРН',
            'category' => 'Категория НВОС',
            'okved' => 'ОКВЭД / сфера',
            'site_name' => 'Основной объект',
            'site_address' => 'Адрес объекта',
            'notes' => 'Дополнительные сведения',
            'contact_name' => 'Контактное лицо',
            'contact_email' => 'Email контакта (логин)',
            'contact_role' => 'Роль / должность',
            'contact_phone' => 'Телефон',
            'access_channels' => 'Доступы в ЛК (ПИК, email)',
            'manager_id' => 'Менеджер клиента',
            'hasAirEmissions' => 'Есть стационарные источники выбросов',
            'hasWasteGeneration' => 'Образуются отходы',
            'hasWaterUse' => 'Есть лицензия на скважину',
            'hasSurfaceWaterIntake' => 'Забор поверхностных вод / сброс',
            'needsInstructionDocs' => 'Требуются инструкции и приказы',
            'needsTrainingProgram' => 'Нужно плановое обучение ответственных',
            'emission_sources' => 'Тип источников выбросов',
            'water_source' => 'Источник водопользования',
            'well_license_number' => 'Номер лицензии на недра',
            'well_license_valid_until' => 'Срок лицензии (если есть)',
        ];
    }

    public function getCategoryOptions(): array
    {
        return [
            'I' => 'I категория (наиболее опасные)',
            'II' => 'II категория',
            'III' => 'III категория',
            'IV' => 'IV категория',
        ];
    }

    public function getEmissionSourceOptions(): array
    {
        return [
            'stationary' => 'Стационарные источники',
            'mobile' => 'Передвижные источники',
            'mixed' => 'Смешанные источники',
            'none' => 'Нет значимых источников',
        ];
    }

    public function getWaterSourceOptions(): array
    {
        return [
            'well' => 'Скважина / недропользование',
            'surface' => 'Поверхностные воды (река/озеро)',
            'mixed' => 'Комбинированный источник',
            'none' => 'Нет водопользования',
        ];
    }

    public function getManagerOptions(): array
    {
        if ($this->_managerOptions === null) {
            $this->_managerOptions = User::find()
                ->select(['id', 'username', 'email'])
                ->where(['role' => [User::ROLE_CLIENT_MANAGER, User::ROLE_ADMIN]])
                ->orderBy(['username' => SORT_ASC])
                ->asArray()
                ->all();
        }

        $options = [];
        foreach ($this->_managerOptions as $manager) {
            $label = $manager['username'] ?: $manager['email'];
            if ($manager['email'] && $manager['email'] !== $manager['username']) {
                $label .= ' (' . $manager['email'] . ')';
            }
            $options[(int)$manager['id']] = $label;
        }

        return $options;
    }

    public function validateManager(): void
    {
        if ($this->manager_id === null || $this->manager_id === '') {
            return;
        }

        $manager = User::find()
            ->where(['id' => (int)$this->manager_id, 'is_active' => true])
            ->andWhere(['role' => [User::ROLE_CLIENT_MANAGER, User::ROLE_ADMIN]])
            ->one();

        if ($manager === null) {
            $this->addError('manager_id', 'Выберите доступного менеджера.');
        }
    }

    public function getSelectedManagerLabel(): string
    {
        if ($this->manager_id === null) {
            return '';
        }

        if ($this->_selectedManagerLabel === null) {
            $manager = User::find()
                ->select(['username', 'email'])
                ->where(['id' => (int)$this->manager_id])
                ->asArray()
                ->one();
            if ($manager) {
                $label = $manager['username'] ?: $manager['email'];
                if ($manager['email'] && $manager['email'] !== $manager['username']) {
                    $label .= ' (' . $manager['email'] . ')';
                }
                $this->_selectedManagerLabel = $label;
            } else {
                $this->_selectedManagerLabel = '';
            }
        }

        return $this->_selectedManagerLabel;
    }
}
