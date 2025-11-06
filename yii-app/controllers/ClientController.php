<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\Client;

class ClientController extends Controller
{
    public function actionView(int $id = 1)
    {
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
