<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "post_vote_answer_trl".
 *
 * @property integer $id
 * @property integer $answer_id
 * @property string $lng
 * @property string $text
 *
 * @property PostVoteAnswer $answer
 */
class PostVoteAnswerTrlDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post_vote_answer_trl';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['answer_id'], 'integer'],
            [['text'], 'string'],
            [['lng'], 'string', 'max' => 5],
            [['answer_id'], 'exist', 'skipOnError' => true, 'targetClass' => PostVoteAnswer::className(), 'targetAttribute' => ['answer_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'answer_id' => 'Answer ID',
            'lng' => 'Lng',
            'text' => 'Text',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAnswer()
    {
        return $this->hasOne(PostVoteAnswer::className(), ['id' => 'answer_id']);
    }
}
