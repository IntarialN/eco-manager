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
                'documents',
                'history.user',
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

        return $this->render('view', [
            'requirement' => $requirement,
            'canManage' => $user->canManageRequirements(),
            'redirect' => Yii::$app->request->url,
            'documentUploadForm' => $this->createDocumentUploadForm(),
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
            $model->file->saveAs($uploadDir . DIRECTORY_SEPARATOR . $fileName);

            $document = new \app\models\Document([
                'client_id' => $requirement->client_id,
                'requirement_id' => $requirement->id,
                'title' => $model->title,
                'type' => $model->type,
                'status' => \app\models\Document::STATUS_PENDING,
                'path' => '/uploads/' . $fileName,
                'uploaded_at' => date('Y-m-d H:i:s'),
            ]);
            $document->save(false);

            Yii::$app->session->setFlash('success', 'Документ загружен и отправлен на проверку.');
        } else {
            Yii::$app->session->setFlash('error', 'Не удалось загрузить документ.');
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
            if ($status === Requirement::STATUS_DONE) {
                $event->status = CalendarEvent::STATUS_DONE;
                $event->completed_at = date('Y-m-d H:i:s');
            } else {
                $event->completed_at = null;
                if ($event->due_date && strtotime($event->due_date) < strtotime('today')) {
                    $event->status = CalendarEvent::STATUS_OVERDUE;
                } else {
                    $event->status = CalendarEvent::STATUS_SCHEDULED;
                }
            }
            $event->save(false);
        }
    }

    private function createDocumentUploadForm()
    {
        $model = new \yii\base\DynamicModel(['file', 'title', 'type']);
        $model->addRule(['title', 'type'], 'required');
        $model->addRule('file', 'file', [
            'extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg'],
            'maxSize' => 10 * 1024 * 1024,
            'skipOnEmpty' => false,
        ]);
        return $model;
    }
}
