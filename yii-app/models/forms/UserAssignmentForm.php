<?php

namespace app\models\forms;

use app\models\Client;
use app\models\User;
use app\models\UserClientAssignment;
use Yii;
use yii\base\Model;
use yii\db\Exception;

class UserAssignmentForm extends Model
{
    public int $userId;
    /** @var int[] */
    public $assignedClientIds = [];

    private User $user;
    private ?array $_clientOptions = null;

    public function __construct(User $user, $config = [])
    {
        $this->user = $user;
        $this->userId = (int)$user->id;
        $this->assignedClientIds = $user->getAssignedClientIds();

        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['userId'], 'required'],
            [['userId'], 'integer'],
            [
                'assignedClientIds',
                'filter',
                'filter' => function ($value) {
                    if ($value === null || $value === '') {
                        return [];
                    }
                    return (array)$value;
                },
            ],
            ['assignedClientIds', 'each', 'rule' => ['integer']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'assignedClientIds' => 'Назначенные клиенты',
        ];
    }

    /**
     * @throws Exception
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            UserClientAssignment::deleteAll(['user_id' => $this->userId]);

            if (!empty($this->assignedClientIds)) {
                $rows = [];
                foreach ($this->assignedClientIds as $clientId) {
                    $rows[] = [$this->userId, (int)$clientId];
                }
                Yii::$app->db->createCommand()
                    ->batchInsert(UserClientAssignment::tableName(), ['user_id', 'client_id'], $rows)
                    ->execute();
            }

            $transaction->commit();
            $this->user->refreshAssignedClients();
            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function getClientsList(): array
    {
        return Client::find()
            ->select(['name', 'registration_number', 'id'])
            ->orderBy(['name' => SORT_ASC])
            ->asArray()
            ->all();
    }

    /**
     * @return array<int, array{name:string,registration_number:?string,id:int}>
     */
    public function getClientOptions(): array
    {
        if ($this->_clientOptions === null) {
            $this->_clientOptions = $this->getClientsList();
        }

        return $this->_clientOptions;
    }
}
