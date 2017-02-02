<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "post_image".
 *
 * @property integer $id
 * @property integer $post_id
 * @property string $file_path
 * @property string $file_url
 * @property integer $is_external
 * @property integer $status_id
 * @property integer $priority
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by_id
 * @property integer $updated_by_id
 * @property string $fb_sync_id
 * @property integer $need_crop
 * @property string $crop_settings
 * @property integer $strict_ratio
 *
 * @property Post $post
 * @property PostImageTrl[] $postImageTrls
 */
class PostImageDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post_image';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id', 'is_external', 'status_id', 'priority', 'created_by_id', 'updated_by_id', 'need_crop', 'strict_ratio'], 'integer'],
            [['file_url'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['file_path', 'fb_sync_id', 'crop_settings'], 'string', 'max' => 255],
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
            'file_path' => 'File Path',
            'file_url' => 'File Url',
            'is_external' => 'Is External',
            'status_id' => 'Status ID',
            'priority' => 'Priority',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
            'fb_sync_id' => 'Fb Sync ID',
            'need_crop' => 'Need Crop',
            'crop_settings' => 'Crop Settings',
            'strict_ratio' => 'Strict Ratio',
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
    public function getPostImageTrls()
    {
        return $this->hasMany(PostImageTrl::className(), ['post_image_id' => 'id']);
    }
}
