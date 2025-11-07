<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use app\models\Client;
use app\models\Requirement;
use app\models\User;
use app\models\forms\ClientIntakeForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Response;
use yii\filters\AccessControl;

class ClientController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionOnboard()
    {
        $identity = Yii::$app->user->identity;
        if (!$identity instanceof User || !$identity->canManageClients()) {
            throw new ForbiddenHttpException('Недостаточно прав для создания клиента.');
        }

        $model = new ClientIntakeForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                /** @var \app\components\RequirementBuilderService $builder */
                $builder = Yii::$app->get('requirementBuilder');
                $result = $builder->onboard($model);
                $this->notifyOnboarding($result['user'], $result['client'], $result['password']);
                Yii::$app->session->setFlash('success', 'Клиент создан, инструкции отправлены на указанный email.');
                return $this->redirect(['client/view', 'id' => $result['client']->id]);
            } catch (\Throwable $e) {
                Yii::error(['message' => 'Onboard failed', 'error' => $e->getMessage()], __METHOD__);
                Yii::$app->session->setFlash('error', 'Не удалось сохранить клиента: ' . $e->getMessage());
            }
        }

        return $this->render('onboard', [
            'model' => $model,
            'showManagerField' => true,
        ]);
    }

    public function actionOnboardSelf()
    {
        $identity = Yii::$app->user->identity;
        if (!$identity instanceof User || $identity->role !== User::ROLE_CLIENT_USER) {
            throw new ForbiddenHttpException('Доступ доступен только клиентам.');
        }
        if ($identity->client_id !== null) {
            return $this->redirect(['client/view', 'id' => $identity->client_id]);
        }

        $model = new ClientIntakeForm();
        $model->existingUserId = $identity->id;
        $model->contact_email = $identity->email;
        $model->contact_name = $identity->username ?? $identity->email;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                /** @var \app\components\RequirementBuilderService $builder */
                $builder = Yii::$app->get('requirementBuilder');
                $result = $builder->onboard($model, $identity);
                $this->notifyOnboarding($result['user'], $result['client'], $result['password']);
                Yii::$app->session->setFlash('success', 'Анкета заполнена. Добро пожаловать в личный кабинет.');
                return $this->redirect(['client/view', 'id' => $result['client']->id]);
            } catch (\Throwable $e) {
                Yii::error(['message' => 'Self onboard failed', 'error' => $e->getMessage()], __METHOD__);
                Yii::$app->session->setFlash('error', 'Не удалось сохранить анкету: ' . $e->getMessage());
            }
        }

        return $this->render('onboard', [
            'model' => $model,
            'showManagerField' => false,
        ]);
    }

    public function actionSelect()
    {
        $user = Yii::$app->user->identity;
        if (!$user instanceof User) {
            return $this->redirect(['site/login']);
        }

        if ($user->role === User::ROLE_CLIENT_USER) {
            $clientId = $user->getDefaultClientId();
            if ($clientId !== null) {
                return $this->redirect(['client/view', 'id' => $clientId]);
            }

            return $this->redirect(['client/onboard-self']);
        }

        $request = Yii::$app->request;
        $query = trim((string)$request->get('q', ''));
        $isAdmin = $user->role === User::ROLE_ADMIN;
        $scope = $isAdmin ? $request->get('scope', 'all') : 'assigned';

        $assignedClientIds = $this->resolveAssignedClientIds($user);
        $showScopeFilter = $isAdmin;

        $clientQuery = Client::find()->alias('c');

        if (!$isAdmin || $scope === 'assigned') {
            if (empty($assignedClientIds)) {
                $clientQuery->andWhere('0=1');
            } else {
                $clientQuery->andWhere(['c.id' => $assignedClientIds]);
            }
        }

        $canSearch = $isAdmin || !empty($assignedClientIds);

        if ($query !== '') {
            $tokens = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($tokens as $token) {
                $clientQuery->andWhere(['or',
                    ['like', 'c.name', $token],
                    ['like', 'c.registration_number', $token],
                    ['like', 'c.category', $token],
                    ['like', 'c.description', $token],
                ]);
            }
        }

        $clients = $clientQuery
            ->orderBy(['c.name' => SORT_ASC])
            ->limit(30)
            ->all();

        return $this->render('select', [
            'clients' => $clients,
            'query' => $query,
            'scope' => $scope,
            'showScopeFilter' => $showScopeFilter,
            'canSearch' => $canSearch,
        ]);
    }

    public function actionView(int $id = 1)
    {
        $user = Yii::$app->user->identity;
        if ($user && !$user->canAccessClient($id)) {
            throw new ForbiddenHttpException('Доступ к данным клиента ограничен.');
        }

        $client = Client::find()->with([
            'sites',
            'requirements.documents',
            'documents',
            'calendarEvents',
            'risks',
            'contracts.invoices',
            'contracts.acts',
        ])->where(['id' => $id])->one();

        if (!$client) {
            throw new NotFoundHttpException('Client not found');
        }

        $requirements = $client->requirements;
        $requirementStatusFilter = Yii::$app->request->get('reqStatus', 'all');
        $requirementStats = $this->buildRequirementStats($requirements);
        $filteredRequirements = $this->filterRequirementsByStatus($requirements, $requirementStatusFilter);

        return $this->render('view', [
            'client' => $client,
            'allRequirements' => $requirements,
            'requirements' => $filteredRequirements,
            'requirementsStats' => $requirementStats,
            'requirementStatusFilter' => $requirementStatusFilter,
        ]);
    }

    /**
     * @param Requirement[] $requirements
     */
    private function buildRequirementStats(array $requirements): array
    {
        $stats = [
            'all' => count($requirements),
            Requirement::STATUS_NEW => 0,
            Requirement::STATUS_IN_PROGRESS => 0,
            Requirement::STATUS_DONE => 0,
            Requirement::STATUS_BLOCKED => 0,
            'overdue' => 0,
            'completedPercent' => 0,
        ];

        foreach ($requirements as $requirement) {
            $status = $requirement->status;
            if (isset($stats[$status])) {
                $stats[$status]++;
            }

            if ($requirement->isOverdue()) {
                $stats['overdue']++;
            }
        }

        if ($stats['all'] > 0) {
            $stats['completedPercent'] = (int) round(($stats[Requirement::STATUS_DONE] / $stats['all']) * 100);
        }

        return $stats;
    }

    /**
     * @param Requirement[] $requirements
     * @return Requirement[]
     */
    private function filterRequirementsByStatus(array $requirements, string $status): array
    {
        if ($status === 'all') {
            return $requirements;
        }

        if ($status === 'overdue') {
            return array_values(array_filter($requirements, fn(Requirement $requirement) => $requirement->isOverdue()));
        }

        return array_values(array_filter($requirements, fn(Requirement $requirement) => $requirement->status === $status));
    }

    private function resolveAssignedClientIds(User $user): array
    {
        $ids = $user->getAssignedClientIds();
        if ($user->client_id !== null) {
            $ids[] = (int)$user->client_id;
        }

        return array_values(array_unique(array_filter($ids, static fn($id) => $id !== null)));
    }

    private function notifyOnboarding(User $user, Client $client, ?string $password): void
    {
        $loginUrl = Url::to(['site/login'], true);
        $credentials = $password
            ? "Логин: <strong>{$user->email}</strong><br>Пароль: <strong>{$password}</strong><br><br>"
            : "Логин: <strong>{$user->email}</strong><br>Пароль — тот, который вы указали при регистрации.<br><br>";

        $body = <<<HTML
Здравствуйте, {$client->name}!<br><br>
Вам создан доступ в Eco Manager.<br>
{$credentials}
Перейдите по ссылке {$loginUrl} и смените пароль после первого входа.
HTML;

        try {
            Yii::$app->mailer->compose()
                ->setTo($user->email)
                ->setFrom([Yii::$app->params['supportEmail'] ?? Yii::$app->params['adminEmail'] => 'Eco Manager'])
                ->setSubject('Доступ к Eco Manager')
                ->setHtmlBody($body)
                ->send();
        } catch (\Throwable $e) {
            Yii::error(['message' => 'Failed to send welcome email', 'error' => $e->getMessage()], __METHOD__);
        }

        $adminEmail = Yii::$app->params['adminEmail'] ?? null;
        if ($adminEmail) {
            try {
                Yii::$app->mailer->compose()
                    ->setTo($adminEmail)
                    ->setFrom([Yii::$app->params['supportEmail'] ?? $adminEmail => 'Eco Manager'])
                    ->setSubject('Создан новый клиент')
                    ->setTextBody("Создан клиент {$client->name}. Логин пользователя: {$user->email}.")
                    ->send();
            } catch (\Throwable $e) {
                Yii::error(['message' => 'Failed to send admin onboarding email', 'error' => $e->getMessage()], __METHOD__);
            }
        }
    }

    public function actionManagerList(string $q = '', int $page = 1): array
    {
        $identity = Yii::$app->user->identity;
        if (!$identity instanceof User || !$identity->canManageClients()) {
            throw new ForbiddenHttpException('Недостаточно прав.');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = User::find()
            ->select(['id', 'username', 'email', 'role'])
            ->where(['role' => [User::ROLE_CLIENT_MANAGER, User::ROLE_ADMIN], 'is_active' => true]);

        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'username', $q],
                ['like', 'email', $q],
            ]);
        }

        $pageSize = 10;
        $total = (int)$query->count();
        $totalPages = (int)max(1, ceil($total / $pageSize));
        $page = max(1, min($page, $totalPages));

        $managers = $query
            ->orderBy(['username' => SORT_ASC])
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->asArray()
            ->all();

        $items = array_map(static function ($manager) {
            return [
                'id' => (int)$manager['id'],
                'label' => $manager['username'] ?: $manager['email'],
                'email' => $manager['email'],
                'role' => User::roleLabels()[$manager['role']] ?? $manager['role'],
            ];
        }, $managers);

        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => $total,
                'totalPages' => $totalPages,
            ],
        ];
    }
}
