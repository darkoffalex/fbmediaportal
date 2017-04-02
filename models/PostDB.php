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
 * @property integer $group_id
 * @property integer $kind_id
 * @property string $offer_category_tag
 * @property string $offer_author_tag
 * @property string $offer_group_fb_id
 * @property string $video_preview_fb
 * @property string $video_preview_yt
 * @property string $video_attachment_id_fb
 * @property integer $need_finish
 * @property integer $comment_count
 * @property integer $about_turkey
 * @property string $last_comment_at
 * @property string $search_keywords
 * @property integer $need_update
 * @property integer $is_parsed
 * @property string $trail
 * @property string $in_sibling_for_cats
 *
 * @property Comment[] $comments
 * @property User $author
 * @property PostGroup $group
 * @property PostCategory[] $postCategories
 * @property Category[] $categories
 * @property PostCategoryTurkey[] $postCategoryTurkeys
 * @property Category[] $categories0
 * @property PostImage[] $postImages
 * @property PostTrl[] $postTrls
 * @property PostVoteAnswer[] $postVoteAnswers
 * @property UserTimeLine[] $userTimeLines
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
            [['fb_sync_id', 'fb_sync_token', 'video_key_yt', 'video_key_fb', 'voted_ips', 'offer_group_fb_id', 'search_keywords'], 'string'],
            [['content_type_id', 'status_id', 'type_id', 'author_id', 'sticky_position_main', 'stats_after_vote', 'votes_only_authorized', 'created_by_id', 'updated_by_id', 'group_id', 'kind_id', 'need_finish', 'comment_count', 'about_turkey', 'need_update', 'is_parsed'], 'integer'],
            [['name'], 'required'],
            [['published_at', 'created_at', 'updated_at', 'last_comment_at'], 'safe'],
            [['name', 'author_custom_name', 'offer_category_tag', 'offer_author_tag', 'video_preview_fb', 'video_preview_yt', 'video_attachment_id_fb', 'trail', 'in_sibling_for_cats'], 'string', 'max' => 255],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => PostGroup::className(), 'targetAttribute' => ['group_id' => 'id']],
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
            'group_id' => 'Group ID',
            'kind_id' => 'Kind ID',
            'offer_category_tag' => 'Offer Category Tag',
            'offer_author_tag' => 'Offer Author Tag',
            'offer_group_fb_id' => 'Offer Group Fb ID',
            'video_preview_fb' => 'Video Preview Fb',
            'video_preview_yt' => 'Video Preview Yt',
            'video_attachment_id_fb' => 'Video Attachment Id Fb',
            'need_finish' => 'Need Finish',
            'comment_count' => 'Comment Count',
            'about_turkey' => 'About Turkey',
            'last_comment_at' => 'Last Comment At',
            'search_keywords' => 'Search Keywords',
            'need_update' => 'Need Update',
            'is_parsed' => 'Is Parsed',
            'trail' => 'Trail',
            'in_sibling_for_cats' => 'In Sibling For Cats',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['post_id' => 'id']);
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
    public function getGroup()
    {
        return $this->hasOne(PostGroup::className(), ['id' => 'group_id']);
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
    public function getPostCategoryTurkeys()
    {
        return $this->hasMany(PostCategoryTurkey::className(), ['post_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories0()
    {
        return $this->hasMany(Category::className(), ['id' => 'category_id'])->viaTable('post_category_turkey', ['post_id' => 'id']);
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserTimeLines()
    {
        return $this->hasMany(UserTimeLine::className(), ['post_id' => 'id']);
    }
}
