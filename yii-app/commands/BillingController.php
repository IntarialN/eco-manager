<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class BillingController extends Controller
{
    public function actionSync(): int
    {
        $summary = Yii::$app->get('billingSync')->syncAll();
        $this->stdout(
            sprintf(
                "Sync completed: %d contracts, %d invoices, %d acts\n",
                $summary['contracts'] ?? 0,
                $summary['invoices'] ?? 0,
                $summary['acts'] ?? 0
            ),
            Console::FG_GREEN
        );

        return ExitCode::OK;
    }
}
