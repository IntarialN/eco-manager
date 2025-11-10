<?php

namespace app\controllers;

use app\models\CallbackRequest;
use app\models\ChatMessage;
use app\models\ChatSession;
use app\models\forms\CallbackRequestForm;
use app\models\forms\ChatMessageForm;
use app\models\forms\ChatSessionForm;
use app\services\ChatService;
use Yii;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class ChatController extends Controller
{
    public $enableCsrfValidation = false;
    private const STAFF_ACTIONS = ['inbox', 'thread', 'assign', 'reply', 'alerts'];

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'message' => ['POST'],
                    'callback' => ['POST'],
                    'view' => ['GET'],
                    'assign' => ['POST'],
                    'reply' => ['POST'],
                    'alerts' => ['GET'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'only' => ['create', 'message', 'callback', 'view', 'alerts'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'only' => self::STAFF_ACTIONS,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => fn() => $this->canManageChats(),
                    ],
                ],
            ],
        ];
    }

    public function actionCreate(): array
    {
        $form = new ChatSessionForm();
        $form->load(Yii::$app->request->bodyParams, '');
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            if ($user instanceof \app\models\User) {
                $form->client_id = $user->getDefaultClientId();
            }
        }

        if (!$form->validate()) {
            Yii::$app->response->setStatusCode(422);
            return ['errors' => $form->errors];
        }

        try {
            $session = $this->getChatService()->openSession($form);
        } catch (\Throwable $e) {
            throw new ServerErrorHttpException($e->getMessage(), 0, $e);
        }

        return $this->serializeSession($session);
    }

    public function actionMessage(int $id): array
    {
        $session = ChatSession::findOne($id);
        if (!$session) {
            throw new NotFoundHttpException('Сессия не найдена');
        }

        $form = new ChatMessageForm([
            'session_id' => $session->id,
        ]);
        $form->load(Yii::$app->request->bodyParams, '');

        if ($form->sender_type === ChatMessage::SENDER_OPERATOR) {
            if (Yii::$app->user->isGuest || !$this->canManageChats()) {
                throw new ForbiddenHttpException('Требуются права модератора');
            }
            $form->sender_id = Yii::$app->user->id;
        }

        if (!$form->validate()) {
            Yii::$app->response->setStatusCode(422);
            return ['errors' => $form->errors];
        }

        try {
            $message = $this->getChatService()->postMessage($form);
        } catch (\Throwable $e) {
            throw new ServerErrorHttpException($e->getMessage(), 0, $e);
        }

        return $this->serializeMessage($message);
    }

    public function actionCallback(int $id): array
    {
        $session = ChatSession::findOne($id);
        if (!$session) {
            throw new NotFoundHttpException('Сессия не найдена');
        }

        $form = new CallbackRequestForm([
            'session_id' => $session->id,
        ]);
        $form->load(Yii::$app->request->bodyParams, '');
        if (!$form->validate()) {
            Yii::$app->response->setStatusCode(422);
            return ['errors' => $form->errors];
        }

        try {
            $callback = $this->getChatService()->requestCallback($form);
        } catch (\Throwable $e) {
            throw new ServerErrorHttpException($e->getMessage(), 0, $e);
        }

        return $this->serializeCallback($callback);
    }

    public function actionView(int $id): array
    {
        $session = ChatSession::findOne($id);
        if (!$session) {
            throw new NotFoundHttpException('Сессия не найдена');
        }

        $sinceId = (int)Yii::$app->request->get('since_id', 0);
        $messagesQuery = ChatMessage::find()
            ->where(['session_id' => $session->id])
            ->orderBy(['id' => SORT_ASC]);

        if ($sinceId > 0) {
            $messagesQuery->andWhere(['>', 'id', $sinceId]);
        } else {
            $messagesQuery->limit(200);
        }

        $messages = $messagesQuery->all();

        return [
            'session' => $this->serializeSession($session),
            'messages' => array_map(fn (ChatMessage $model) => $this->serializeMessage($model), $messages),
        ];
    }

    public function actionAlerts(?string $since = null): array
    {
        if (!$this->canManageChats()) {
            throw new ForbiddenHttpException('Недостаточно прав');
        }

        $userId = Yii::$app->user->id;
        $latestNew = ChatSession::find()
            ->where(['status' => ChatSession::openStatuses(), 'assigned_user_id' => null])
            ->max('updated_at');
        $hasNewDialogs = false;
        if ($latestNew && $since) {
            $hasNewDialogs = strtotime($latestNew) > strtotime($since);
        } elseif ($latestNew && !$since) {
            $hasNewDialogs = true;
        }

        $hasMyUpdates = ChatSession::find()
            ->where(['assigned_user_id' => $userId])
            ->andWhere(['status' => ChatSession::openStatuses()])
            ->andWhere(['or',
                ['assigned_seen_at' => null],
                new Expression('COALESCE([[last_message_at]], [[updated_at]], [[created_at]]) > [[assigned_seen_at]]'),
            ])
            ->exists();

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'latest_new' => $latestNew,
            'has_new_dialogs' => $hasNewDialogs,
            'has_my_updates' => $hasMyUpdates,
            'unassigned' => (int)ChatSession::find()
                ->where(['status' => ChatSession::openStatuses(), 'assigned_user_id' => null])
                ->count(),
        ];
    }

    public function actionInbox()
    {
        if (!$this->canManageChats()) {
            throw new ForbiddenHttpException('Недостаточно прав');
        }

        $userId = Yii::$app->user->id;

        $mySessions = ChatSession::find()
            ->where(['assigned_user_id' => $userId])
            ->orderBy(['updated_at' => SORT_DESC])
            ->all();

        $openSessions = ChatSession::find()
            ->where(['status' => ChatSession::openStatuses()])
            ->andWhere(['assigned_user_id' => null])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $closedSessions = ChatSession::find()
            ->where(['status' => ChatSession::STATUS_CLOSED])
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit(100)
            ->all();

        return $this->render('inbox', [
            'mySessions' => $mySessions,
            'openSessions' => $openSessions,
            'closedSessions' => $closedSessions,
        ]);
    }

    public function actionThread(int $id)
    {
        $session = ChatSession::findOne($id);
        if (!$session) {
            throw new NotFoundHttpException('Сессия не найдена');
        }

        $messages = $session->messages;
        if ($session->assigned_user_id && $session->assigned_user_id === Yii::$app->user->id) {
            $session->assigned_seen_at = new Expression('CURRENT_TIMESTAMP');
            $session->save(false, ['assigned_seen_at', 'updated_at']);
        }

        return $this->render('thread', [
            'session' => $session,
            'messages' => $messages,
        ]);
    }

    public function actionAssign(int $id): Response
    {
        $session = ChatSession::findOne($id);
        if (!$session) {
            throw new NotFoundHttpException('Сессия не найдена');
        }
        $session->assigned_user_id = Yii::$app->user->id;
        $session->assigned_seen_at = new Expression('CURRENT_TIMESTAMP');
        $session->save(false, ['assigned_user_id', 'assigned_seen_at', 'updated_at']);
        Yii::$app->session->setFlash('success', 'Чат закреплён за вами');
        return $this->redirect(['thread', 'id' => $session->id]);
    }

    public function actionReply(int $id): Response
    {
        $session = ChatSession::findOne($id);
        if (!$session) {
            throw new NotFoundHttpException('Сессия не найдена');
        }

        $body = Yii::$app->request->post('message');
        if (!$body) {
            Yii::$app->session->setFlash('error', 'Введите сообщение');
            return $this->redirect(['thread', 'id' => $session->id]);
        }

        try {
            $form = new ChatMessageForm([
                'session_id' => $session->id,
                'sender_type' => ChatMessage::SENDER_OPERATOR,
                'sender_id' => Yii::$app->user->id,
                'direction' => ChatMessage::DIRECTION_BOT_TO_WEB,
                'body' => $body,
            ]);
            $this->getChatService()->postMessage($form);
            Yii::$app->session->setFlash('success', 'Сообщение отправлено');
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['thread', 'id' => $session->id]);
    }

    private function serializeSession(ChatSession $session): array
    {
        return [
            'id' => $session->id,
            'client_id' => $session->client_id,
            'name' => $session->name,
            'source' => $session->source,
            'status' => $session->status,
            'initiator' => $session->initiator,
            'external_contact' => $session->external_contact,
            'last_message_at' => $session->last_message_at,
            'created_at' => $session->created_at,
            'assigned_user_id' => $session->assigned_user_id,
        ];
    }

    private function serializeMessage(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'session_id' => $message->session_id,
            'sender_type' => $message->sender_type,
            'sender_id' => $message->sender_id,
            'body' => $message->body,
            'direction' => $message->direction,
            'attachments' => $message->attachments ? Json::decode($message->attachments) : [],
            'created_at' => $message->created_at,
            'id' => $message->id,
        ];
    }

    private function serializeCallback(CallbackRequest $request): array
    {
        return [
            'id' => $request->id,
            'session_id' => $request->session_id,
            'phone' => $request->phone,
            'status' => $request->status,
            'preferred_time' => $request->preferred_time,
            'comment' => $request->comment,
        ];
    }

    private function getChatService(): ChatService
    {
        /** @var ChatService $service */
        $service = Yii::$app->get('chatService');
        return $service;
    }

    private function canManageChats(): bool
    {
        $identity = Yii::$app->user->identity;
        return $identity instanceof \app\models\User && $identity->canManageClients();
    }
}
