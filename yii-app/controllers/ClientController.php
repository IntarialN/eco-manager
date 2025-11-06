<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use app\models\Client;
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

        return $this->render('view', [
            'client' => $client,
        ]);
    }
}
