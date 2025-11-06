<?php

namespace app\controllers;

use app\models\User;
use app\models\forms\UserAssignmentForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class UserController extends Controller
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
                            return $identity && $identity->role === User::ROLE_ADMIN;
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $users = User::find()
            ->with(['client', 'assignedClients'])
            ->orderBy(['username' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'users' => $users,
        ]);
    }

    public function actionUpdate(int $id): string|Response
    {
        $user = $this->findModel($id);

        $form = new UserAssignmentForm($user);
        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            Yii::$app->session->setFlash('success', 'Назначения обновлены.');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'user' => $user,
            'model' => $form,
        ]);
    }

    private function findModel(int $id): User
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден.');
        }

        return $user;
    }
}
