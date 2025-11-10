<?php

namespace app\commands;

use app\models\ChatSession;
use DateInterval;
use DateTimeImmutable;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Expression;

class ChatMaintenanceController extends Controller
{
    /**
     * Minutes of inactivity after which a session is considered archived.
     */
    public int $timeout = 30;

    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), ['timeout']);
    }

    /**
     * Archive inactive chat sessions (status = closed).
     */
    public function actionArchive(): int
    {
        $cutoff = (new DateTimeImmutable())->sub(new DateInterval('PT' . $this->timeout . 'M'));
        $cutoffString = $cutoff->format('Y-m-d H:i:s');

        $query = ChatSession::find()
            ->where(['status' => [ChatSession::STATUS_OPEN]])
            ->andWhere(['<', new Expression('COALESCE([[last_message_at]], [[created_at]])'), $cutoffString]);

        $sessions = $query->all();
        $count = 0;
        foreach ($sessions as $session) {
            /** @var ChatSession $session */
            $session->status = ChatSession::STATUS_CLOSED;
            if ($session->save(false, ['status', 'updated_at'])) {
                $count++;
            }
        }

        $this->stdout("Archived {$count} sessions older than {$this->timeout} minutes.\n");

        return ExitCode::OK;
    }
}
