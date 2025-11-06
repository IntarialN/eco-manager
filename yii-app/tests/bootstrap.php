<?php

declare(strict_types=1);

use yii\BaseYii;

define('YII_ENV', 'test');
define('YII_DEBUG', true);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

BaseYii::setAlias('@app', dirname(__DIR__));
BaseYii::setAlias('@tests', __DIR__);
