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
        $form->hasSurfaceWaterIntake = true;
        $form->hazardous_waste_present = true;
        $form->hazardous_substances_class = 'I';
        $form->annual_emissions_tons = 12.5;
        $form->annual_waste_kg = 750;
        $form->livestock_byproducts = true;
        $form->responsible_person_count = 2;
        $form->responsible_person_trained = false;
        $form->needsInstructionDocs = false;

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

        $codes = array_map(static fn(Requirement $requirement) => $requirement->code, $requirements);
        self::assertContains('REQ-01', $codes);
        self::assertContains('REQ-03', $codes);
        self::assertContains('REQ-11', $codes);
        self::assertContains('REQ-WATER-02', $codes);
        self::assertContains('REQ-TRAINING-RESP', $codes);
        self::assertNotContains('REQ-20', $codes);
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

    public function testInstructionRequirementTriggeredByManualFlag(): void
    {
        $form = new ClientIntakeForm();
        $form->name = 'ООО Инструкции';
        $form->registration_number = '5098765432';
        $form->category = 'IV';
        $form->site_name = 'Склад';
        $form->site_address = 'г. Тула, ул. Индустриальная, 5';
        $form->contact_name = 'Пётр Петров';
        $form->contact_email = 'manual@example.com';
        $form->hasWasteGeneration = false;
        $form->hasAirEmissions = false;
        $form->needsInstructionDocs = true;
        $form->responsible_person_count = 0;

        self::assertTrue($form->validate());

        $result = Yii::$app->requirementBuilder->onboard($form);
        $codes = Requirement::find()
            ->select('code')
            ->where(['client_id' => $result['client']->id])
            ->column();

        self::assertContains('REQ-11', $codes);
    }
}
