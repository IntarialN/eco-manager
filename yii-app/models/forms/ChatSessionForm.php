<?php

namespace app\models\forms;

use app\models\ChatSession;
use yii\base\Model;

class ChatSessionForm extends Model
{
    public ?int $client_id = null;
    public ?string $external_contact = null;
    public ?string $name = null;
    public string $source = ChatSession::SOURCE_WEB;
    public string $initiator = ChatSession::INITIATOR_CLIENT;
    public string $status = ChatSession::STATUS_OPEN;
    public ?string $initial_message = null;

    public function rules(): array
    {
        return [
            [['source', 'initiator'], 'required'],
            [['client_id'], 'integer'],
            [['external_contact', 'name'], 'string', 'max' => 128],
            [['initial_message'], 'string'],
            [['source'], 'in', 'range' => array_keys(ChatSession::sourceLabels())],
            [['initiator'], 'in', 'range' => array_keys(ChatSession::initiatorLabels())],
            [['status'], 'in', 'range' => array_keys(ChatSession::statusLabels())],
            ['external_contact', 'required', 'when' => fn () => $this->client_id === null, 'whenClient' => "function(){return true;}"],
            ['external_contact', 'email', 'when' => fn () => $this->isEmail($this->external_contact ?? '')],
        ];
    }

    private function isEmail(string $value): bool
    {
        return str_contains($value, '@');
    }
}
