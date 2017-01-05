<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "post_vote_answer".
 *
 * @property integer $id
 * @property integer $post_id
 * @property integer $voted_qnt
 * @property integer $priority
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by_id
 * @property integer $updated_by_id
 *
 * @property Post $post
 * @property PostVoteAnswerTrl[] $postVoteAnswerTrls
 */
class PostVoteAnswerDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post_vote_answer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id', 'voted_qnt', 'priority', 'created_by_id', 'updated_by_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::className(), 'targetAttribute' => ['post_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_id' => 'Post ID',
            'voted_qnt' => 'Voted Qnt',
            'priority' => 'Priority',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Post::className(), ['id' => 'post_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostVoteAnswerTrls()
    {
        return $this->hasMany(PostVoteAnswerTrl::className(), ['answer_id' => 'id']);
    }
}
