<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ErrorAction;

class SiteController extends Controller
{
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
        return $this->redirect(['client/view', 'id' => 1]);
    }
}
