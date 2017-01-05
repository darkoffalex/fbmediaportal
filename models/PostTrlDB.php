<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "post_trl".
 *
 * @property integer $id
 * @property integer $post_id
 * @property string $lng
 * @property string $name
 * @property string $small_text
 * @property string $text
 * @property string $question
 *
 * @property Post $post
 */
class PostTrlDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post_trl';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id'], 'integer'],
            [['small_text', 'text', 'question'], 'string'],
            [['lng'], 'string', 'max' => 5],
            [['name'], 'string', 'max' => 255],
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
            'lng' => 'Lng',
            'name' => 'Name',
            'small_text' => 'Small Text',
            'text' => 'Text',
            'question' => 'Question',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Post::className(), ['id' => 'post_id']);
    }
}
