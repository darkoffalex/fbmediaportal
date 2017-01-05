<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "post".
 *
 * @property integer $id
 * @property string $fb_sync_id
 * @property string $fb_sync_token
 * @property integer $content_type_id
 * @property integer $status_id
 * @property integer $type_id
 * @property string $name
 * @property integer $author_id
 * @property string $author_custom_name
 * @property string $source_url
 * @property integer $sticky_position_main
 * @property integer $stats_after_vote
 * @property integer $votes_only_authorized
 * @property string $video_key_yt
 * @property string $video_key_fb
 * @property string $published_at
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by_id
 * @property integer $updated_by_id
 * @property string $voted_ips
 *
 * @property User $author
 * @property PostCategory[] $postCategories
 * @property Category[] $categories
 * @property PostImage[] $postImages
 * @property PostTrl[] $postTrls
 * @property PostVoteAnswer[] $postVoteAnswers
 */
class PostDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fb_sync_id', 'fb_sync_token', 'source_url', 'video_key_yt', 'video_key_fb', 'voted_ips'], 'string'],
            [['content_type_id', 'status_id', 'type_id', 'author_id', 'sticky_position_main', 'stats_after_vote', 'votes_only_authorized', 'created_by_id', 'updated_by_id'], 'integer'],
            [['name'], 'required'],
            [['published_at', 'created_at', 'updated_at'], 'safe'],
            [['name', 'author_custom_name'], 'string', 'max' => 255],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fb_sync_id' => 'Fb Sync ID',
            'fb_sync_token' => 'Fb Sync Token',
            'content_type_id' => 'Content Type ID',
            'status_id' => 'Status ID',
            'type_id' => 'Type ID',
            'name' => 'Name',
            'author_id' => 'Author ID',
            'author_custom_name' => 'Author Custom Name',
            'source_url' => 'Source Url',
            'sticky_position_main' => 'Sticky Position Main',
            'stats_after_vote' => 'Stats After Vote',
            'votes_only_authorized' => 'Votes Only Authorized',
            'video_key_yt' => 'Video Key Yt',
            'video_key_fb' => 'Video Key Fb',
            'published_at' => 'Published At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
            'voted_ips' => 'Voted Ips',
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
    public function getPostCategories()
    {
        return $this->hasMany(PostCategory::className(), ['post_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['id' => 'category_id'])->viaTable('post_category', ['post_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostImages()
    {
        return $this->hasMany(PostImage::className(), ['post_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostTrls()
    {
        return $this->hasMany(PostTrl::className(), ['post_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostVoteAnswers()
    {
        return $this->hasMany(PostVoteAnswer::className(), ['post_id' => 'id']);
    }
}