<?php

namespace app\controllers;

use app\models\forms\RiskActionPlanForm;
use app\models\Risk;
use app\models\RiskActionPlan;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RiskController extends Controller
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
                        'matchCallback' => function () {
                            $identity = Yii::$app->user->identity;
                            return $identity instanceof User && $identity->canManageRequirements();
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionView(int $id): string
    {
        $risk = $this->findRisk($id);
        $form = new RiskActionPlanForm();

        return $this->render('view', [
            'risk' => $risk,
            'planForm' => $form,
            'users' => $this->getAssignableUsers(),
        ]);
    }

    public function actionAddPlan(int $id): Response
    {
        $risk = $this->findRisk($id);
        $form = new RiskActionPlanForm();
        $form->load(Yii::$app->request->post());

        $user = Yii::$app->user->identity;
        $userId = $user instanceof User ? (int)$user->id : null;

        if ($plan = $form->save($risk)) {
            $risk->addLog(
                'plan_task_created',
                sprintf('Добавлена задача "%s"', $plan->task),
                $userId
            );
            $statusChanged = $risk->refreshStatusFromPlans($userId, 'После добавления задачи');
            $this->notifyRisk($risk, 'plan_task_created', [
                'task' => $plan->task,
                'ownerId' => $plan->owner_id,
                'dueDate' => $plan->due_date,
            ]);
            if ($statusChanged !== null) {
                $this->notifyRisk($risk, 'risk_status_changed', [
                    'newStatus' => $statusChanged,
                ]);
            }
            Yii::$app->session->setFlash('success', 'Задача добавлена в план действий.');
        } else {
            Yii::$app->session->setFlash('error', 'Не удалось добавить задачу: ' . implode(' ', $form->getFirstErrors()));
        }

        return $this->redirect(['view', 'id' => $risk->id]);
    }

    public function actionUpdatePlanStatus(int $id): Response
    {
        $plan = RiskActionPlan::findOne($id);
        if (!$plan) {
            throw new NotFoundHttpException('Задача плана не найдена.');
        }

        $risk = $this->findRisk((int)$plan->risk_id);

        $status = (string)Yii::$app->request->post('status');
        if (!array_key_exists($status, RiskActionPlan::statusLabels())) {
            Yii::$app->session->setFlash('error', 'Недопустимый статус.');
            return $this->redirect(['view', 'id' => $risk->id]);
        }

        $oldStatus = $plan->status;
        $plan->status = $status;
        $plan->updated_at = date('Y-m-d H:i:s');
        if ($plan->save(false)) {
            $user = Yii::$app->user->identity;
            $userId = $user instanceof User ? (int)$user->id : null;

            $risk->addLog(
                'plan_task_status_updated',
                sprintf(
                    'Задача "%s": статус изменён с "%s" на "%s"',
                    $plan->task,
                    RiskActionPlan::statusLabels()[$oldStatus] ?? $oldStatus,
                    RiskActionPlan::statusLabels()[$plan->status] ?? $plan->status
                ),
                $userId
            );
            $statusChanged = $risk->refreshStatusFromPlans($userId, 'После обновления задачи');
            $this->notifyRisk($risk, 'plan_task_status_updated', [
                'task' => $plan->task,
                'oldStatus' => $oldStatus,
                'newStatus' => $plan->status,
            ]);
            if ($statusChanged !== null) {
                $this->notifyRisk($risk, 'risk_status_changed', [
                    'newStatus' => $statusChanged,
                ]);
            }
            Yii::$app->session->setFlash('success', 'Статус задачи обновлён.');
        } else {
            Yii::$app->session->setFlash('error', 'Не удалось обновить статус задачи.');
        }

        return $this->redirect(['view', 'id' => $risk->id]);
    }

    private function findRisk(int $id): Risk
    {
        $risk = Risk::find()
            ->with([
                'client',
                'requirement',
                'actionPlans.owner',
                'logs.user',
            ])
            ->where(['id' => $id])
            ->one();

        if (!$risk) {
            throw new NotFoundHttpException('Риск не найден.');
        }

        $user = Yii::$app->user->identity;
        if (!$user instanceof User || !$user->canAccessClient((int)$risk->client_id)) {
            throw new NotFoundHttpException('Доступ запрещён.');
        }

        return $risk;
    }

    private function getAssignableUsers(): array
    {
        return User::find()
            ->select(['username', 'id'])
            ->where(['is_active' => true])
            ->orderBy(['username' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }

    private function notifyRisk(Risk $risk, string $event, array $payload = []): void
    {
        if (Yii::$app->has('notificationService')) {
            /** @var \app\components\NotificationService $notifier */
            $notifier = Yii::$app->get('notificationService');
            $notifier->sendRiskUpdate($risk, $event, $payload);
        }
    }
}
