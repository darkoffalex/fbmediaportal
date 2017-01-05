<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "post_image_trl".
 *
 * @property integer $id
 * @property integer $post_image_id
 * @property string $lng
 * @property string $signature
 * @property string $name
 *
 * @property PostImage $postImage
 */
class PostImageTrlDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post_image_trl';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_image_id'], 'integer'],
            [['lng'], 'string', 'max' => 5],
            [['signature', 'name'], 'string', 'max' => 255],
            [['post_image_id'], 'exist', 'skipOnError' => true, 'targetClass' => PostImage::className(), 'targetAttribute' => ['post_image_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_image_id' => 'Post Image ID',
            'lng' => 'Lng',
            'signature' => 'Signature',
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostImage()
    {
        return $this->hasOne(PostImage::className(), ['id' => 'post_image_id']);
    }
}
