<?php

namespace app\models;

use Yii;
use yii\base\Model;

class RegisterForm extends Model
{
    public string $email = '';
    public string $password = '';
    public string $password_repeat = '';

    public function rules(): array
    {
        return [
            [['email', 'password', 'password_repeat'], 'required'],
            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            [
                'email',
                'unique',
                'targetClass' => User::class,
                'targetAttribute' => ['email' => 'email'],
                'message' => 'Пользователь с таким email уже зарегистрирован.',
            ],
            ['password', 'string', 'min' => 8, 'tooShort' => 'Пароль должен содержать минимум 8 символов.'],
            [
                'password_repeat',
                'compare',
                'compareAttribute' => 'password',
                'message' => 'Пароли должны совпадать.',
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'email' => 'Email',
            'password' => 'Пароль',
            'password_repeat' => 'Повторите пароль',
        ];
    }

    public function validateRole(): void
    {
        if ($this->role === '') {
            return;
        }
        $options = $this->getRoleOptions();
        if (!array_key_exists($this->role, $options)) {
            $this->addError('role', 'Выберите допустимую роль.');
        }
    }

    public function getRoleOptions(): array
    {
        $allowed = Yii::$app->params['registration']['allowedRoles'] ?? [
            User::ROLE_CLIENT_USER,
            User::ROLE_CLIENT_MANAGER,
        ];
        $labels = User::roleLabels();
        return array_filter($labels, fn($label, $role) => in_array($role, $allowed, true), ARRAY_FILTER_USE_BOTH);
    }

    public function register(): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->email;
        $user->email = $this->email;
        $user->role = User::ROLE_CLIENT_USER;
        $user->is_active = true;
        $user->created_at = time();
        $user->updated_at = time();
        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->password_hash = Yii::$app->security->generatePasswordHash($this->password);
        $user->generateEmailConfirmToken();

        if (!$user->save()) {
            $this->addErrors($user->getFirstErrors());
            return null;
        }

        return $user;
    }
}
