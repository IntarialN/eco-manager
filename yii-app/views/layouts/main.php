<?php
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?php $isLoginPage = Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'login'; ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'Eco Manager',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar navbar-expand-lg navbar-dark bg-dark',
        ],
    ]);
    $identity = Yii::$app->user->identity;
    $menuItems = [];
    if (!Yii::$app->user->isGuest) {
        if ($identity instanceof \app\models\User && $identity->canManageClients()) {
            $menuItems[] = ['label' => 'Добавить клиента', 'url' => ['/client/onboard']];
        }
        if ($identity->role === \app\models\User::ROLE_ADMIN) {
            $menuItems[] = ['label' => 'Пользователи', 'url' => ['/user/index']];
        }
        $menuItems[] = '<li class="nav-item ms-lg-3">' .
            Html::tag('span', Html::encode($identity->username) . ' · ' . Html::encode($identity->getRoleLabel()), [
                'class' => 'navbar-text text-white-50',
            ]) .
            '</li>';
        $menuItems[] = '<li class="nav-item">' .
            Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline']) .
            Html::submitButton('Выход', ['class' => 'btn btn-sm btn-outline-light ms-lg-3']) .
            Html::endForm() .
            '</li>';
    } else {
        $menuItems[] = ['label' => 'Войти', 'url' => ['/site/login']];
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav ms-auto align-items-center gap-2'],
        'items' => $menuItems,
        'encodeLabels' => false,
    ]);
    NavBar::end();
    ?>

    <main class="main-content<?= $isLoginPage ? ' main-content--centered' : '' ?>">
        <div class="container<?= $isLoginPage ? ' login-container' : ' mt-4' ?>">
            <?= $content ?>
        </div>
    </main>
</div>

<footer class="footer py-3 bg-light">
    <div class="container">
        <p class="text-muted mb-0">&copy; Eco Manager <?= date('Y') ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
