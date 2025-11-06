<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use app\models\Client;
use app\models\Requirement;
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
}
