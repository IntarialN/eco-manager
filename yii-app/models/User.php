<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property int|null $client_id
 * @property string $username
 * @property string $email
 * @property string $role
 * @property string $password_hash
 * @property string $auth_key
 * @property string|null $access_token
 * @property bool $is_active
 * @property string|null $last_login_at
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Client|null $client
 */
class User extends ActiveRecord implements IdentityInterface
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_CLIENT_MANAGER = 'client_manager';
    public const ROLE_PROJECT_SPECIALIST = 'project_specialist';
    public const ROLE_CLIENT_USER = 'client_user';
    public const ROLE_FINANCE_MANAGER = 'finance_manager';
    public const ROLE_AUDITOR = 'auditor';
    public const ROLE_EXTERNAL_VIEWER = 'external_viewer';

    private ?array $_assignedClientIds = null;

    public static function tableName(): string
    {
        return '{{%user}}';
    }

    public function rules(): array
    {
        return [
            [['username', 'email', 'role', 'password_hash', 'auth_key'], 'required'],
            [['username', 'email'], 'string', 'max' => 255],
            [['username', 'email'], 'unique'],
            [['role'], 'string', 'max' => 64],
            [['auth_key'], 'string', 'max' => 32],
            [['access_token'], 'string', 'max' => 64],
            [['client_id'], 'integer'],
            [['is_active'], 'boolean'],
            [['last_login_at'], 'safe'],
            [['created_at', 'updated_at'], 'integer'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username' => 'Логин',
            'email' => 'Email',
            'role' => 'Роль',
            'client_id' => 'Клиент',
        ];
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        return static::findOne(['id' => $id, 'is_active' => true]);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return static::findOne(['access_token' => $token, 'is_active' => true]);
    }

    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['username' => $username, 'is_active' => true]);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    public function getClientAssignments()
    {
        return $this->hasMany(UserClientAssignment::class, ['user_id' => 'id']);
    }

    public function getAssignedClients()
    {
        return $this->hasMany(Client::class, ['id' => 'client_id'])->via('clientAssignments');
    }

    public function getAssignedClientIds(): array
    {
        if ($this->_assignedClientIds === null) {
            $this->_assignedClientIds = $this->getAssignedClients()->select('id')->column();
            $this->_assignedClientIds = array_map('intval', $this->_assignedClientIds);
        }

        return $this->_assignedClientIds;
    }

    public function refreshAssignedClients(): void
    {
        $this->_assignedClientIds = null;
    }

    public function canAccessClient(int $clientId): bool
    {
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }

        if ($this->client_id === null) {
            return in_array($clientId, $this->getAssignedClientIds(), true);
        }

        if ((int)$this->client_id === $clientId) {
            return true;
        }

        return in_array($clientId, $this->getAssignedClientIds(), true);
    }

    public function getDefaultClientId(): ?int
    {
        if ($this->client_id !== null) {
            return (int)$this->client_id;
        }

        $assigned = $this->getAssignedClientIds();

        return $assigned[0] ?? null;
    }

    public function canManageRequirements(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            self::ROLE_CLIENT_MANAGER,
            self::ROLE_PROJECT_SPECIALIST,
        ], true);
    }

    public function getRoleLabel(): string
    {
        return self::roleLabels()[$this->role] ?? $this->role;
    }

    public static function roleLabels(): array
    {
        return [
            self::ROLE_ADMIN => 'Администратор',
            self::ROLE_CLIENT_MANAGER => 'Менеджер клиента',
            self::ROLE_PROJECT_SPECIALIST => 'Специалист',
            self::ROLE_CLIENT_USER => 'Представитель клиента',
            self::ROLE_FINANCE_MANAGER => 'Финансовый менеджер',
            self::ROLE_AUDITOR => 'Аудитор',
            self::ROLE_EXTERNAL_VIEWER => 'Внешний просмотр',
        ];
    }
}
