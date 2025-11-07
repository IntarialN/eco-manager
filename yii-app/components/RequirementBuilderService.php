<?php

namespace app\components;

use app\models\Client;
use app\models\Requirement;
use app\models\Site;
use app\models\User;
use app\models\UserClientAssignment;
use app\models\forms\ClientIntakeForm;
use RuntimeException;
use Yii;
use yii\base\Component;
use yii\db\Exception;

class RequirementBuilderService extends Component
{
    /**
     * @throws Exception
     */
    /**
     * @return array{client: Client, user: User, password: ?string}
     * @throws Exception
     */
    public function onboard(ClientIntakeForm $form, ?User $existingUser = null): array
    {
        if (!$form->validate()) {
            throw new RuntimeException('Анкета заполнена с ошибками.');
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $client = new Client([
                'name' => $form->name,
                'registration_number' => $form->registration_number,
                'category' => $form->category,
                'description' => $this->buildClientDescription($form),
            ]);
            if (!$client->save()) {
                throw new RuntimeException('Не удалось создать клиента: ' . json_encode($client->getFirstErrors()));
            }

            $site = new Site([
                'client_id' => $client->id,
                'name' => $form->site_name,
                'address' => $form->site_address,
                'emission_category' => $form->category,
            ]);
            if (!$site->save()) {
                throw new RuntimeException('Не удалось создать объект: ' . json_encode($site->getFirstErrors()));
            }

            $plainPassword = null;
            if ($existingUser) {
                $user = $this->attachExistingUser($existingUser, $client);
            } else {
                $plainPassword = $this->generateInitialPassword();
                $user = $this->createClientUser($client->id, $form->contact_email, $plainPassword);
            }

            if ($form->manager_id) {
                $this->assignManager((int)$form->manager_id, (int)$client->id);
            }

            foreach ($this->getRequirementTemplates($form) as $template) {
                if (isset($template['condition']) && !$template['condition']) {
                    continue;
                }

                $requirement = new Requirement([
                    'client_id' => $client->id,
                    'site_id' => (($template['siteScope'] ?? null) === 'primary') ? $site->id : null,
                    'code' => $template['code'],
                    'title' => $template['title'],
                    'category' => $template['category'],
                    'status' => Requirement::STATUS_NEW,
                    'due_date' => $this->resolveDueDate($template['due_in_days'] ?? null),
                ]);

                if (!$requirement->save()) {
                    throw new RuntimeException('Не удалось создать требование: ' . json_encode($requirement->getFirstErrors()));
                }
            }

            $transaction->commit();
            return [
                'client' => $client,
                'user' => $user,
                'password' => $plainPassword,
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function resolveDueDate(?int $days): ?string
    {
        if ($days === null) {
            return null;
        }

        return date('Y-m-d', strtotime("+{$days} days"));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getRequirementTemplates(ClientIntakeForm $form): array
    {
        return [
            [
                'code' => 'REQ-WASTE-JOURNAL',
                'title' => 'Вести журнал учёта движения отходов',
                'category' => 'waste',
                'due_in_days' => 30,
                'siteScope' => 'primary',
                'condition' => $form->hasWasteGeneration,
            ],
            [
                'code' => 'REQ-AIR-REPORT',
                'title' => 'Подготовить декларацию по выбросам',
                'category' => 'air',
                'due_in_days' => 60,
                'siteScope' => 'primary',
                'condition' => $form->hasAirEmissions,
            ],
            [
                'code' => 'REQ-WATER-LICENSE',
                'title' => 'Продлить лицензию на недропользование (скважина)',
                'category' => 'water',
                'due_in_days' => 90,
                'condition' => $form->hasWaterUse || in_array($form->water_source, ['well', 'mixed'], true),
            ],
            [
                'code' => 'REQ-SURFACE-DISCHARGE',
                'title' => 'Обновить разрешение на сброс сточных вод',
                'category' => 'water',
                'due_in_days' => 120,
                'condition' => $form->hasSurfaceWaterIntake || in_array($form->water_source, ['surface', 'mixed'], true),
            ],
            [
                'code' => 'REQ-INSTRUCTION',
                'title' => 'Обновить инструкции и приказы по обращению с отходами',
                'category' => 'training',
                'due_in_days' => 45,
                'condition' => $form->needsInstructionDocs,
            ],
            [
                'code' => 'REQ-TRAINING',
                'title' => 'Организовать обучение ответственных за экологию',
                'category' => 'training',
                'due_in_days' => 75,
                'condition' => $form->needsTrainingProgram,
            ],
        ];
    }

    private function generateInitialPassword(): string
    {
        return Yii::$app->security->generateRandomString(12);
    }

    private function createClientUser(int $clientId, string $email, string $plainPassword): User
    {
        $user = new User([
            'client_id' => $clientId,
            'username' => $email,
            'email' => $email,
            'role' => User::ROLE_CLIENT_USER,
            'password_hash' => Yii::$app->security->generatePasswordHash($plainPassword),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'is_active' => true,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        if (!$user->save()) {
            throw new RuntimeException('Не удалось создать пользователя клиента: ' . json_encode($user->getFirstErrors()));
        }

        return $user;
    }

    private function assignManager(int $managerId, int $clientId): void
    {
        $exists = UserClientAssignment::find()
            ->where(['user_id' => $managerId, 'client_id' => $clientId])
            ->exists();

        if ($exists) {
            return;
        }

        $assignment = new UserClientAssignment([
            'user_id' => $managerId,
            'client_id' => $clientId,
        ]);
        if (!$assignment->save()) {
            throw new RuntimeException('Не удалось назначить менеджера: ' . json_encode($assignment->getFirstErrors()));
        }
    }

    private function attachExistingUser(User $user, Client $client): User
    {
        $user->client_id = $client->id;
        $user->updated_at = time();
        if (!$user->save(false, ['client_id', 'updated_at'])) {
            throw new RuntimeException('Не удалось привязать пользователя к клиенту.');
        }

        return $user;
    }

    private function buildClientDescription(ClientIntakeForm $form): string
    {
        $lines = [];
        $lines[] = 'ОКВЭД / сфера: ' . ($form->okved ?: 'не указано');
        $lines[] = 'Контакт: ' . trim($form->contact_name . ($form->contact_role ? " ({$form->contact_role})" : ''));
        if ($form->contact_phone) {
            $lines[] = 'Телефон: ' . $form->contact_phone;
        }
        if ($form->access_channels) {
            $lines[] = 'Доступы: ' . $form->access_channels;
        }
        if ($form->emission_sources) {
            $lines[] = 'Источники выбросов: ' . $this->humanizeOption($form->emission_sources, $form->getEmissionSourceOptions());
        }
        if ($form->water_source) {
            $lines[] = 'Источник водопользования: ' . $this->humanizeOption($form->water_source, $form->getWaterSourceOptions());
        }
        if ($form->well_license_number) {
            $label = 'Лицензия на недра: ' . $form->well_license_number;
            if ($form->well_license_valid_until) {
                $label .= ' (до ' . $form->well_license_valid_until . ')';
            }
            $lines[] = $label;
        }
        if ($form->notes) {
            $lines[] = 'Заметки: ' . $form->notes;
        }

        return implode("\n", array_filter($lines));
    }

    private function humanizeOption(string $value, array $options): string
    {
        return $options[$value] ?? $value;
    }
}
