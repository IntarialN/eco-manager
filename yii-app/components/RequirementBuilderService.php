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
                'annual_emissions_tons' => $form->annual_emissions_tons ?? 0,
                'annual_waste_kg' => $form->annual_waste_kg ?? 0,
                'hazardous_waste_present' => (bool)$form->hazardous_waste_present,
                'hazardous_substances_class' => $form->hazardous_substances_class ?: null,
                'has_well' => (bool)$form->hasWaterUse,
                'uses_surface_water' => (bool)$form->hasSurfaceWaterIntake,
                'livestock_byproducts' => (bool)$form->livestock_byproducts,
                'responsible_person_trained' => (bool)$form->responsible_person_trained,
                'responsible_person_count' => $form->responsible_person_count,
                'instruction_docs_required' => (bool)$form->needsInstructionDocs,
                'water_source' => $form->water_source ?: null,
                'training_valid_until' => $form->training_valid_until ?: null,
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
        $definitions = $this->requirementDefinitions();
        $result = [];

        foreach ($definitions as $definition) {
            $condition = $definition['condition'];
            if (is_callable($condition) && !$condition($form)) {
                continue;
            }
            $result[] = $definition;
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function requirementDefinitions(): array
    {
        return [
            [
                'code' => 'REQ-01',
                'title' => 'Журнал учёта движения отходов производства и потребления',
                'category' => 'waste',
                'due_in_days' => 30,
                'siteScope' => 'primary',
                'condition' => fn () => true,
            ],
            [
                'code' => 'REQ-02',
                'title' => 'Журнал учёта стационарных источников выбросов',
                'category' => 'air',
                'due_in_days' => 45,
                'siteScope' => 'primary',
                'condition' => fn (ClientIntakeForm $form) => $this->categoryIn($form, ['I', 'II', 'III']),
            ],
            [
                'code' => 'REQ-03',
                'title' => 'Статотчётность 2-ТП (воздух)',
                'category' => 'air',
                'due_in_days' => 90,
                'siteScope' => 'primary',
                'condition' => fn (ClientIntakeForm $form) => $this->toFloat($form->annual_emissions_tons) > 5,
            ],
            [
                'code' => 'REQ-04',
                'title' => 'Статотчётность 2-ТП (отходы)',
                'category' => 'waste',
                'due_in_days' => 90,
                'siteScope' => 'primary',
                'condition' => fn (ClientIntakeForm $form) => $this->toFloat($form->annual_waste_kg) > 100,
            ],
            [
                'code' => 'REQ-05',
                'title' => 'Декларация о плате за НВОС',
                'category' => 'payments',
                'due_in_days' => 120,
                'condition' => fn (ClientIntakeForm $form) => $this->categoryIn($form, ['I', 'II', 'III']),
            ],
            [
                'code' => 'REQ-06',
                'title' => 'Отчёт по программе производственного экологического контроля',
                'category' => 'compliance',
                'due_in_days' => 150,
                'condition' => fn (ClientIntakeForm $form) => $this->categoryIn($form, ['I', 'II', 'III']),
            ],
            [
                'code' => 'REQ-08',
                'title' => 'Отчёт инвентаризации выбросов загрязняющих веществ',
                'category' => 'air',
                'due_in_days' => 120,
                'siteScope' => 'primary',
                'condition' => fn () => true,
            ],
            [
                'code' => 'REQ-09',
                'title' => 'Отчёт инвентаризации отходов',
                'category' => 'waste',
                'due_in_days' => 120,
                'siteScope' => 'primary',
                'condition' => fn () => true,
            ],
            [
                'code' => 'REQ-10',
                'title' => 'Паспорта отходов I–IV класса опасности',
                'category' => 'waste',
                'due_in_days' => 90,
                'siteScope' => 'primary',
                'condition' => fn (ClientIntakeForm $form) => $form->hazardous_waste_present || $form->hasWasteGeneration,
            ],
            [
                'code' => 'REQ-11',
                'title' => 'Инструкции и приказы по обращению с отходами',
                'category' => 'training',
                'due_in_days' => 60,
                'siteScope' => 'primary',
                'condition' => fn (ClientIntakeForm $form) => $this->instructionDocsRequired($form),
            ],
            [
                'code' => 'REQ-12',
                'title' => 'НООЛР (нормативы образования отходов)',
                'category' => 'waste',
                'due_in_days' => 150,
                'condition' => fn (ClientIntakeForm $form) => $this->categoryIn($form, ['I', 'II']),
            ],
            [
                'code' => 'REQ-13',
                'title' => 'Нормативы допустимых выбросов (НДВ)',
                'category' => 'air',
                'due_in_days' => 180,
                'condition' => fn (ClientIntakeForm $form) => $this->categoryIn($form, ['I', 'II']),
            ],
            [
                'code' => 'REQ-14',
                'title' => 'НДВ для особо опасных веществ',
                'category' => 'air',
                'due_in_days' => 200,
                'condition' => fn (ClientIntakeForm $form) => $this->categoryIn($form, ['III'])
                    && $this->hasHazardousSubstances($form),
            ],
            [
                'code' => 'REQ-15',
                'title' => 'Экспертное заключение на проект НДВ',
                'category' => 'air',
                'due_in_days' => 210,
                'condition' => fn (ClientIntakeForm $form) => $this->categoryIn($form, ['I', 'II', 'III']),
            ],
            [
                'code' => 'REQ-16',
                'title' => 'Санитарно-эпидемиологическое заключение на проект НДВ',
                'category' => 'air',
                'due_in_days' => 210,
                'condition' => fn (ClientIntakeForm $form) => $this->categoryIn($form, ['I', 'II', 'III']),
            ],
            [
                'code' => 'REQ-17',
                'title' => 'План мероприятий на НМУ',
                'category' => 'air',
                'due_in_days' => 60,
                'condition' => fn (ClientIntakeForm $form) => $this->categoryIn($form, ['I', 'II', 'III']),
            ],
            [
                'code' => 'REQ-18',
                'title' => 'Программа производственного экологического контроля (ПЭК)',
                'category' => 'compliance',
                'due_in_days' => 90,
                'condition' => fn (ClientIntakeForm $form) => $this->categoryIn($form, ['I', 'II', 'III']),
            ],
            [
                'code' => 'REQ-19',
                'title' => 'Декларация о воздействии на окружающую среду (ДВОС)',
                'category' => 'compliance',
                'due_in_days' => 120,
                'condition' => fn (ClientIntakeForm $form) => $this->categoryIn($form, ['II']),
            ],
            [
                'code' => 'REQ-20',
                'title' => 'Комплексное экологическое разрешение (КЭР)',
                'category' => 'compliance',
                'due_in_days' => 240,
                'condition' => fn (ClientIntakeForm $form) => $this->categoryIn($form, ['I']),
            ],
            [
                'code' => 'REQ-21',
                'title' => 'Проект санитарно-защитной зоны',
                'category' => 'compliance',
                'due_in_days' => 200,
                'condition' => fn () => true,
            ],
            [
                'code' => 'REQ-22',
                'title' => 'Решение об установлении санитарно-защитной зоны',
                'category' => 'compliance',
                'due_in_days' => 200,
                'condition' => fn () => true,
            ],
            [
                'code' => 'REQ-WATER-01',
                'title' => 'Продлить лицензию на недропользование (скважина)',
                'category' => 'water',
                'due_in_days' => 120,
                'condition' => fn (ClientIntakeForm $form) => $this->hasWell($form),
            ],
            [
                'code' => 'REQ-WATER-02',
                'title' => 'Обновить разрешение на водопользование (поверхностные воды)',
                'category' => 'water',
                'due_in_days' => 150,
                'condition' => fn (ClientIntakeForm $form) => $this->hasSurfaceWater($form),
            ],
            [
                'code' => 'REQ-BYPRODUCT-01',
                'title' => 'Технические условия на побочную продукцию животноводства',
                'category' => 'waste',
                'due_in_days' => 90,
                'condition' => fn (ClientIntakeForm $form) => (bool)$form->livestock_byproducts,
            ],
            [
                'code' => 'REQ-TRAINING-RESP',
                'title' => 'Организовать обучение ответственных за экологию',
                'category' => 'training',
                'due_in_days' => 75,
                'condition' => fn (ClientIntakeForm $form) => $this->trainingRequired($form),
            ],
        ];
    }

    private function categoryIn(ClientIntakeForm $form, array $categories): bool
    {
        if ($form->category === '') {
            return false;
        }

        $normalized = $this->normalizeCategory($form->category);
        $targets = array_map(fn ($value) => $this->normalizeCategory($value), $categories);

        return in_array($normalized, $targets, true);
    }

    private function normalizeCategory(string $category): string
    {
        $value = strtoupper(trim($category));

        return match (true) {
            str_starts_with($value, 'I ') || $value === 'I' => 'I',
            str_starts_with($value, 'II') || $value === 'II' => 'II',
            str_starts_with($value, 'III') || $value === 'III' => 'III',
            str_starts_with($value, 'IV') || $value === 'IV' => 'IV',
            default => $value,
        };
    }

    private function toFloat(?float $value): float
    {
        return $value !== null ? (float)$value : 0.0;
    }

    private function hasHazardousSubstances(ClientIntakeForm $form): bool
    {
        return in_array($form->hazardous_substances_class, ['I', 'II'], true);
    }

    private function hasWell(ClientIntakeForm $form): bool
    {
        return (bool)$form->hasWaterUse || in_array($form->water_source, ['well', 'mixed'], true);
    }

    private function hasSurfaceWater(ClientIntakeForm $form): bool
    {
        return (bool)$form->hasSurfaceWaterIntake || in_array($form->water_source, ['surface', 'mixed'], true);
    }

    private function instructionDocsRequired(ClientIntakeForm $form): bool
    {
        $hasWasteActivity = $form->hazardous_waste_present
            || $form->hasWasteGeneration
            || $this->toFloat($form->annual_waste_kg) > 0;

        if (!$hasWasteActivity) {
            return (bool)$form->needsInstructionDocs;
        }

        return $hasWasteActivity && ($form->responsible_person_count > 0 || $form->needsInstructionDocs);
    }

    private function trainingRequired(ClientIntakeForm $form): bool
    {
        if ($form->needsTrainingProgram) {
            return true;
        }

        if ($form->responsible_person_count <= 0) {
            return false;
        }

        if (!$form->responsible_person_trained) {
            return true;
        }

        if (!$form->training_valid_until) {
            return false;
        }

        return $this->certificateExpiresSoon($form->training_valid_until);
    }

    private function certificateExpiresSoon(?string $date): bool
    {
        if (!$date) {
            return false;
        }

        $expiresAt = strtotime($date);
        if ($expiresAt === false) {
            return false;
        }

        $threshold = strtotime('+60 days');

        return $expiresAt <= $threshold;
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
