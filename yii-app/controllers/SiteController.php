<?php
namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ErrorAction;
use app\models\LoginForm;
use app\models\RegisterForm;
use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;

class SiteController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
        ];
    }

    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['login']);
        }

        return $this->redirect($this->getClientHomeRoute());
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect($this->getClientHomeRoute());
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $identity = Yii::$app->user->identity;
            if ($identity instanceof User && $identity->email_confirmed_at === null) {
                Yii::$app->session->setFlash('info', 'Email ещё не подтверждён. Мы всё равно создали доступ, но подтвердите email при первой возможности.');
            }
            return $this->redirect($this->getClientHomeRoute());
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionRegister()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect($this->getClientHomeRoute());
        }

        $model = new RegisterForm();
        if ($model->load(Yii::$app->request->post()) && ($user = $model->register())) {
            Yii::$app->user->login($user);
            Yii::$app->session->setFlash('success', 'Аккаунт создан. Заполните данные клиента, чтобы получить доступ к кабинету.');
            return $this->redirect(['client/onboard-self']);
        }

        return $this->render('register', [
            'model' => $model,
        ]);
    }

    public function actionConfirmEmail(string $token)
    {
        $user = User::find()->where(['email_confirm_token' => $token])->one();
        if (!$user) {
            Yii::$app->session->setFlash('error', 'Ссылка недействительна или устарела.');
            return $this->redirect(['site/login']);
        }

        if ($user->isEmailConfirmed()) {
            Yii::$app->session->setFlash('info', 'Email уже подтверждён.');
            return $this->redirect(['site/login']);
        }

        if ($user->confirmEmail()) {
            Yii::$app->session->setFlash('success', 'Email подтверждён.');
            Yii::$app->user->login($user);
            return $this->redirect($this->getClientHomeRoute());
        }

        Yii::$app->session->setFlash('error', 'Не удалось подтвердить email.');
        return $this->redirect(['site/login']);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect(['site/login']);
    }

    private function resolveDefaultClientId(): ?int
    {
        $user = Yii::$app->user->identity;
        if ($user instanceof User) {
            $clientId = $user->getDefaultClientId();
            if ($clientId !== null) {
                return (int)$clientId;
            }
        }

        return null;
    }

    private function sendRegistrationEmail(User $user): bool
    {
        $confirmLink = Url::to(['site/confirm-email', 'token' => $user->email_confirm_token], true);
        try {
            return Yii::$app->mailer->compose()
                ->setTo($user->email)
                ->setFrom([Yii::$app->params['supportEmail'] ?? Yii::$app->params['adminEmail'] => 'Eco Manager'])
                ->setSubject('Подтверждение регистрации')
                ->setHtmlBody("Здравствуйте!<br>Для активации аккаунта перейдите по ссылке: <a href=\"{$confirmLink}\">Подтвердить email</a>.<br>Если письмо не пришло, проверьте папку «Спам».")
                ->send();
        } catch (\Throwable $e) {
            Yii::error(['message' => 'Mail send failed', 'error' => $e->getMessage()], __METHOD__);
            return false;
        }
    }

    private function getClientHomeRoute(): array
    {
        $clientId = $this->resolveDefaultClientId();
        if ($clientId === null) {
            return ['client/select'];
        }

        return ['client/view', 'id' => $clientId];
    }
}
