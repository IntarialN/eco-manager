<?php

namespace app\models\forms;

use app\models\CallbackRequest;
use app\models\ChatSession;
use yii\base\Model;

class CallbackRequestForm extends Model
{
    public int $session_id;
    public string $phone = '';
    public ?string $preferred_time = null;
    public ?string $comment = null;

    public function rules(): array
    {
        return [
            [['session_id', 'phone'], 'required'],
            [['session_id'], 'integer'],
            [['preferred_time', 'comment'], 'safe'],
            [['phone'], 'string', 'max' => 32],
            ['session_id', 'exist', 'targetClass' => ChatSession::class, 'targetAttribute' => ['session_id' => 'id']],
        ];
    }

    public function toAttributes(): array
    {
        return [
            'session_id' => $this->session_id,
            'phone' => $this->phone,
            'preferred_time' => $this->preferred_time,
            'comment' => $this->comment,
            'status' => CallbackRequest::STATUS_PENDING,
        ];
    }
}
