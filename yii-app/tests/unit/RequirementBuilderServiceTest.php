<?php

declare(strict_types=1);

namespace tests\unit;

use app\models\Client;
use app\models\Requirement;
use app\models\User;
use app\models\forms\ClientIntakeForm;
use Yii;

final class RequirementBuilderServiceTest extends ControllerTestCase
{
    public function testOnboardCreatesClientWithRequirements(): void
    {
        $form = new ClientIntakeForm();
        $form->name = 'ООО Новый Клиент';
        $form->registration_number = '7722334455';
        $form->category = 'II';
        $form->site_name = 'Основной цех';
        $form->site_address = 'Московская область, д. Тестовая, 1';
        $form->contact_name = 'Иван Иванов';
        $form->contact_email = 'new.client@example.com';
        $form->manager_id = 2;
        $form->hasWasteGeneration = true;
        $form->hasAirEmissions = true;
        $form->hasWaterUse = true;
        $form->needsInstructionDocs = true;

        self::assertTrue($form->validate());

        $result = Yii::$app->requirementBuilder->onboard($form);
        $client = $result['client'];
        $user = $result['user'];
        $password = $result['password'];

        self::assertInstanceOf(Client::class, $client);
        self::assertNotNull($client->id);
        self::assertSame('7722334455', $client->registration_number);

        self::assertSame($client->id, $user->client_id);
        self::assertSame($form->contact_email, $user->email);
        self::assertTrue($user->validatePassword($password));

        $requirements = Requirement::find()->where(['client_id' => $client->id])->all();
        self::assertNotEmpty($requirements);

        $hasWasteRequirement = false;
        foreach ($requirements as $requirement) {
            if ($requirement->code === 'REQ-WASTE-JOURNAL') {
                $hasWasteRequirement = true;
                break;
            }
        }
        self::assertTrue($hasWasteRequirement);
    }

    public function testOnboardAttachesExistingUser(): void
    {
        $existingUser = new User([
            'username' => 'self.client@example.com',
            'email' => 'self.client@example.com',
            'role' => User::ROLE_CLIENT_USER,
            'password_hash' => 'hash',
            'auth_key' => 'key',
            'is_active' => true,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $existingUser->save(false);

        $form = new ClientIntakeForm();
        $form->existingUserId = $existingUser->id;
        $form->name = 'ООО Самоклиент';
        $form->registration_number = '1234567890';
        $form->category = 'III';
        $form->site_name = 'Самообъект';
        $form->site_address = 'Адрес';
        $form->contact_name = 'Сам Клиент';
        $form->contact_email = 'self.client@example.com';

        self::assertTrue($form->validate());

        $result = Yii::$app->requirementBuilder->onboard($form, $existingUser);
        self::assertNull($result['password']);
        self::assertSame($existingUser->id, $result['user']->id);
        self::assertSame($result['client']->id, $result['user']->client_id);
    }
}
