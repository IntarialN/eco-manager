<?php

namespace app\controllers;

use app\models\Requirement;
use app\models\User;
use app\models\RequirementHistory;
use app\models\Risk;
use app\models\CalendarEvent;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class RequirementController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
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

    public function actionUpdateStatus(): Response
    {
        $request = Yii::$app->request;
        $requirementId = (int)$request->post('id');
        $status = (string)$request->post('status');
        $redirectUrl = $request->post('redirect');
        $comment = trim((string)$request->post('comment', ''));

        $requirement = Requirement::findOne($requirementId);
        if (!$requirement) {
            throw new NotFoundHttpException('Требование не найдено.');
        }

        if (!$redirectUrl) {
            $redirectUrl = ['/client/view', 'id' => $requirement->client_id];
        }

        $user = Yii::$app->user->identity;
        if (!$user || !$user->canAccessClient((int)$requirement->client_id)) {
            throw new NotFoundHttpException('Доступ запрещён.');
        }

        if (!array_key_exists($status, Requirement::statusLabels())) {
            Yii::$app->session->setFlash('error', 'Недопустимый статус.');
            return $this->redirect($redirectUrl);
        }

        if ($requirement->status === $status) {
            Yii::$app->session->setFlash('info', 'Статус уже установлен.');
            return $this->redirect($redirectUrl);
        }

        $oldStatus = $requirement->status;
        $requirement->status = $status;
        $requirement->completed_at = $status === Requirement::STATUS_DONE ? date('Y-m-d') : null;

        if ($requirement->save(true, ['status', 'completed_at'])) {
            $history = new RequirementHistory([
                'requirement_id' => $requirement->id,
                'user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'comment' => $comment ?: null,
            ]);
            $history->save(false);

            $this->syncRisk($requirement, $status);
            $this->syncCalendar($requirement, $status);

            Yii::$app->session->setFlash('success', 'Статус требования обновлён.');
        } else {
            Yii::$app->session->setFlash('error', 'Не удалось обновить статус.');
        }

        return $this->redirect($redirectUrl);
    }

    public function actionView(int $id)
    {
        $requirement = Requirement::find()
            ->with([
                'client',
                'site',
                'documents.auditor',
            ])
            ->where(['id' => $id])
            ->one();

        if (!$requirement) {
            throw new NotFoundHttpException('Требование не найдено.');
        }

        $user = Yii::$app->user->identity;
        if (!$user || !$user->canAccessClient((int)$requirement->client_id)) {
            throw new NotFoundHttpException('Доступ запрещён.');
        }

        $request = Yii::$app->request;
        $historyDate = trim((string)$request->get('historyDate', ''));
        $historyStatus = trim((string)$request->get('historyStatus', ''));
        $historyComment = trim((string)$request->get('historyComment', ''));
        $historyPage = max(1, (int)$request->get('historyPage', 1));
        $historyPageSize = 10;

        $historyQuery = RequirementHistory::find()
            ->where(['requirement_id' => $requirement->id])
            ->with('user')
            ->orderBy(['created_at' => SORT_DESC]);

        if ($historyDate !== '') {
            $date = \DateTime::createFromFormat('Y-m-d', $historyDate);
            if ($date) {
                $start = $date->format('Y-m-d 00:00:00');
                $end = $date->format('Y-m-d 23:59:59');
                $historyQuery->andWhere(['between', 'created_at', $start, $end]);
            }
        }

        if ($historyStatus !== '' && array_key_exists($historyStatus, Requirement::statusLabels())) {
            $historyQuery->andWhere(['new_status' => $historyStatus]);
        }

        if ($historyComment !== '') {
            $historyQuery->andWhere(['like', 'comment', $historyComment]);
        }

        $historyCountQuery = clone $historyQuery;
        $historyTotal = (int)$historyCountQuery->count();
        $historyTotalPages = (int)max(1, ceil($historyTotal / $historyPageSize));
        $historyPage = min($historyPage, $historyTotalPages);

        $historyItems = $historyQuery
            ->offset(($historyPage - 1) * $historyPageSize)
            ->limit($historyPageSize)
            ->all();

        return $this->render('view', [
            'requirement' => $requirement,
            'canManage' => $user->canManageRequirements(),
            'redirect' => Yii::$app->request->url,
            'documentUploadForm' => $this->createDocumentUploadForm(),
            'historyItems' => $historyItems,
            'historyPagination' => [
                'page' => $historyPage,
                'pageSize' => $historyPageSize,
                'total' => $historyTotal,
                'totalPages' => $historyTotalPages,
                'date' => $historyDate,
                'status' => $historyStatus,
                'comment' => $historyComment,
            ],
            'historyStatuses' => Requirement::statusLabels(),
        ]);
    }

    public function actionUploadDocument(int $id): Response
    {
        $requirement = Requirement::findOne($id);
        if (!$requirement) {
            throw new NotFoundHttpException('Требование не найдено.');
        }

        $user = Yii::$app->user->identity;
        if (!$user || !$user->canManageRequirements() || !$user->canAccessClient((int)$requirement->client_id)) {
            throw new NotFoundHttpException('Доступ запрещён.');
        }

        $model = $this->createDocumentUploadForm();
        $model->load(Yii::$app->request->post());
        $model->file = UploadedFile::getInstance($model, 'file');

        if ($model->validate()) {
            $uploadDir = Yii::getAlias('@app/web/uploads');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $fileName = uniqid('req_' . $requirement->id . '_') . '.' . $model->file->extension;
            $model->file->saveAs($uploadDir . DIRECTORY_SEPARATOR . $fileName, false);

            $reviewMode = $model->review_mode;
            $status = $reviewMode === \app\models\Document::REVIEW_MODE_AUDIT
                ? \app\models\Document::STATUS_PENDING
                : \app\models\Document::STATUS_APPROVED;

            $document = new \app\models\Document([
                'client_id' => $requirement->client_id,
                'requirement_id' => $requirement->id,
                'title' => $model->title,
                'type' => $model->type,
                'status' => $status,
                'review_mode' => $reviewMode,
                'path' => '/uploads/' . $fileName,
                'uploaded_at' => date('Y-m-d H:i:s'),
            ]);

            if ($status === \app\models\Document::STATUS_APPROVED) {
                $document->audit_completed_at = date('Y-m-d H:i:s');
            }

            $document->save(false);
            Yii::$app->notificationService->sendDocumentUploaded($document);

            if ($reviewMode === \app\models\Document::REVIEW_MODE_AUDIT) {
                Yii::$app->session->setFlash('success', 'Документ загружен и отправлен на аудит специалисту.');
            } else {
                Yii::$app->session->setFlash('success', 'Документ загружен и сохранён без аудита.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Не удалось загрузить документ.');
            Yii::$app->session->setFlash('uploadErrors', $model->errors);
            Yii::error([
                'message' => 'Document upload validation failed',
                'errors' => $model->errors,
            ], __METHOD__);
        }

        return $this->redirect(['view', 'id' => $requirement->id]);
    }

    public function actionApproveDocument(int $id): Response
    {
        $document = \app\models\Document::findOne($id);
        if (!$document) {
            throw new NotFoundHttpException('Документ не найден.');
        }

        $user = Yii::$app->user->identity;
        if (!$user || !$user->canManageRequirements() || !$user->canAccessClient((int)$document->client_id)) {
            throw new NotFoundHttpException('Доступ запрещён.');
        }

        $document->status = \app\models\Document::STATUS_APPROVED;
        $document->auditor_id = $user->id;
        $document->audit_completed_at = date('Y-m-d H:i:s');
        $document->save(false);

        Yii::$app->session->setFlash('success', 'Документ подтверждён.');
        return $this->redirect(['view', 'id' => $document->requirement_id]);
    }

    public function actionRejectDocument(int $id): Response
    {
        $document = \app\models\Document::findOne($id);
        if (!$document) {
            throw new NotFoundHttpException('Документ не найден.');
        }

        $user = Yii::$app->user->identity;
        if (!$user || !$user->canManageRequirements() || !$user->canAccessClient((int)$document->client_id)) {
            throw new NotFoundHttpException('Доступ запрещён.');
        }

        $document->status = \app\models\Document::STATUS_REJECTED;
        $document->auditor_id = $user->id;
        $document->audit_completed_at = date('Y-m-d H:i:s');
        $document->save(false);

        Yii::$app->session->setFlash('success', 'Документ отклонён.');
        return $this->redirect(['view', 'id' => $document->requirement_id]);
    }

    private function syncRisk(Requirement $requirement, string $status): void
    {
        $risk = Risk::find()
            ->where(['requirement_id' => $requirement->id])
            ->one();

        if (!$risk) {
            return;
        }

        if ($status === Requirement::STATUS_DONE) {
            $risk->status = 'closed';
        } elseif ($status === Requirement::STATUS_NEW || $status === Requirement::STATUS_BLOCKED) {
            $risk->status = 'open';
        } elseif ($status === Requirement::STATUS_IN_PROGRESS) {
            $risk->status = 'mitigation';
        }

        $risk->save(false);
    }

    private function syncCalendar(Requirement $requirement, string $status): void
    {
        $events = CalendarEvent::find()
            ->where(['requirement_id' => $requirement->id])
            ->all();

        foreach ($events as $event) {
            $oldStatus = $event->status;
            if ($status === Requirement::STATUS_DONE) {
                if ($event->status === CalendarEvent::STATUS_DONE) {
                    continue;
                }
                $event->status = CalendarEvent::STATUS_DONE;
                $event->completed_at = date('Y-m-d H:i:s');
                $event->save(false);
                if ($event->isRecurring()) {
                    $this->scheduleNextCalendarEvent($event);
                }
            } else {
                if ($event->status === CalendarEvent::STATUS_DONE) {
                    continue;
                }
                $event->completed_at = null;
                if ($event->due_date && strtotime($event->due_date) < strtotime('today')) {
                    $event->status = CalendarEvent::STATUS_OVERDUE;
                } else {
                    $event->status = CalendarEvent::STATUS_SCHEDULED;
                }
                $event->save(false);
            }
            if ($oldStatus !== $event->status) {
                Yii::$app->notificationService->sendCalendarEventStatus($event);
            }
        }
    }

    private function scheduleNextCalendarEvent(CalendarEvent $event): void
    {
        $nextDueDate = $event->getNextDueDate();
        if (!$nextDueDate) {
            return;
        }

        $duplicate = CalendarEvent::find()
            ->where([
                'client_id' => $event->client_id,
                'requirement_id' => $event->requirement_id,
                'title' => $event->title,
                'due_date' => $nextDueDate,
            ])
            ->one();

        if ($duplicate) {
            return;
        }

        $newEvent = new CalendarEvent([
            'client_id' => $event->client_id,
            'requirement_id' => $event->requirement_id,
            'title' => $event->title,
            'type' => $event->type,
            'status' => CalendarEvent::STATUS_SCHEDULED,
            'due_date' => $nextDueDate,
            'start_date' => $event->start_date ?? $event->due_date,
            'periodicity' => $event->periodicity,
            'custom_interval_days' => $event->custom_interval_days,
            'reminder_days' => $event->reminder_days,
        ]);

        $newEvent->save(false);
    }

    private function createDocumentUploadForm()
    {
        $model = new \yii\base\DynamicModel(['file', 'title', 'type', 'review_mode']);
        $model->review_mode = \app\models\Document::REVIEW_MODE_AUDIT;
        $model->addRule(['title', 'type', 'review_mode'], 'required');
        $model->addRule('review_mode', 'in', [
            'range' => array_keys(\app\models\Document::reviewModeLabels()),
        ]);
        $model->addRule('file', 'file', [
            'extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg'],
            'maxSize' => 10 * 1024 * 1024,
            'skipOnEmpty' => false,
            'checkExtensionByMimeType' => false,
        ]);
        return $model;
    }
}
