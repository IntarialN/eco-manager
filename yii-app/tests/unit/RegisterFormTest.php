<?php

declare(strict_types=1);

namespace tests\unit;

use app\models\RegisterForm;
use app\models\User;
use Yii;

final class RegisterFormTest extends ControllerTestCase
{
    public function testSuccessfulRegistrationCreatesUser(): void
    {
        $form = new RegisterForm();
        $form->email = 'new.user@example.com';
        $form->password = 'Password#123';
        $form->password_repeat = 'Password#123';

        $user = $form->register();

        self::assertInstanceOf(User::class, $user);
        self::assertSame('new.user@example.com', $user->email);
        self::assertSame('new.user@example.com', $user->username);
        self::assertSame(User::ROLE_CLIENT_USER, $user->role);
        self::assertTrue($user->is_active);
        self::assertNotEmpty($user->email_confirm_token);
        self::assertTrue(Yii::$app->security->validatePassword('Password#123', $user->password_hash));

        $user->confirmEmail();
        self::assertTrue($user->is_active);
        self::assertNull($user->email_confirm_token);
    }
}
