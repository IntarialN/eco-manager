<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $requirement_id
 * @property int|null $user_id
 * @property string|null $old_status
 * @property string $new_status
 * @property string|null $comment
 * @property string $created_at
 *
 * @property Requirement $requirement
 * @property User|null $user
 */
class RequirementHistory extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%requirement_history}}';
    }

    public function rules(): array
    {
        return [
            [['requirement_id', 'new_status'], 'required'],
            [['requirement_id', 'user_id'], 'integer'],
            [['comment'], 'string'],
            [['created_at'], 'safe'],
            [['old_status', 'new_status'], 'string', 'max' => 50],
        ];
    }

    public function getRequirement()
    {
        return $this->hasOne(Requirement::class, ['id' => 'requirement_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
