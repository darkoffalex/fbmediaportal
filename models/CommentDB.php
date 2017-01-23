<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "comment".
 *
 * @property integer $id
 * @property integer $post_id
 * @property integer $author_id
 * @property string $text
 * @property integer $answer_to_id
 * @property string $fb_sync_id
 * @property string $fb_sync_token
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by_id
 * @property integer $updated_by_id
 * @property string $answer_to_fb_id
 *
 * @property User $author
 * @property Post $post
 */
class CommentDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id', 'author_id', 'answer_to_id', 'created_by_id', 'updated_by_id'], 'integer'],
            [['text', 'fb_sync_id', 'fb_sync_token'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['answer_to_fb_id'], 'string', 'max' => 255],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
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
            'author_id' => 'Author ID',
            'text' => 'Text',
            'answer_to_id' => 'Answer To ID',
            'fb_sync_id' => 'Fb Sync ID',
            'fb_sync_token' => 'Fb Sync Token',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
            'answer_to_fb_id' => 'Answer To Fb ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Post::className(), ['id' => 'post_id']);
    }
}
