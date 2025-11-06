<?php

namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private ?User $_user = null;

    public function rules(): array
    {
        return [
            [['username', 'password'], 'required', 'message' => 'Поле «{attribute}» обязательно для заполнения.'],
            ['username', 'match', 'pattern' => '/^[A-Za-z0-9_.-]+$/u', 'message' => 'Логин может содержать только латинские буквы, цифры и символы ._-'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username' => 'Логин',
            'password' => 'Пароль',
            'rememberMe' => 'Запомнить меня',
        ];
    }

    public function validatePassword(string $attribute, $params = []): void
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->getUser();
        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError($attribute, 'Неверная пара логин/пароль.');
        }
    }

    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $duration = $this->rememberMe ? 3600 * 24 * 30 : 0;
        $loggedIn = Yii::$app->user->login($this->getUser(), $duration);

        if ($loggedIn) {
            $this->getUser()->updateAttributes([
                'last_login_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $loggedIn;
    }

    public function getUser(): ?User
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
