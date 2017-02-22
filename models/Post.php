<?php

namespace app\models;

use app\helpers\Constants;
use app\helpers\Help;
use app\models\Comment;
use himiklab\thumbnail\EasyThumbnailImage;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * @property PostTrl $trl
 * @property PostTrl $aTrl
 */
class Post extends PostDB
{
    /**
     * @var int sort flag
     */
    public $stickyFlag = null;

    /**
     * @var array for loading translations from POST
     */
    public $translations = [];

    /**
     * @var array for loading categories relation info
     */
    public $categoriesChecked = [];

    /**
     * @var array for setting sticky positions for every category relation
     */
    public $categoriesStickyPositions = [];

    /**
     * @var bool
     */
    public $translateLabels = true;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $baseLabels = parent::attributeLabels();
        foreach($baseLabels as $attribute => $label){
            $baseLabels[$attribute] = $this->translateLabels ? Yii::t('admin',$label) : $label;
        }
        return $baseLabels;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $baseRules = parent::rules();
        $baseRules[] = [['translations','categoriesChecked','categoriesStickyPositions'],'safe'];
        return $baseRules;
    }

    /**
     * @param string|null $lng
     * @return PostTrl
     */
    public function getATrl($lng = null)
    {
        $lng = empty($lng) ? Yii::$app->language : $lng;

        /* @var $trl PostTrl */
        $trl = PostTrl::findOne(['post_id' => $this->id, 'lng' => $lng]);

        if(empty($trl)){
            $trl = new PostTrl();
            $trl -> post_id = $this->id;
            $trl -> lng = $lng;
        }

        return $trl;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrl()
    {
        $lng = Yii::$app->language;
        return $this->hasOne(PostTrl::className(), ['post_id' => 'id'])->where(['lng' => $lng]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostImages()
    {
        $relation = parent::getPostImages();
        return $relation->orderBy('priority ASC');
    }

    /**
     * Thumbnail URL
     * @param int $w
     * @param int $h
     * @param bool $placeholder
     * @return string
     */
    public function getThumbnailUrl($w = 90, $h = 70, $placeholder = true)
    {
        return !empty($this->postImages[0]) ? $this->postImages[0]->getThumbnailUrl($w,$h) : ($placeholder ? "http://placehold.it/{$w}x{$h}" : EasyThumbnailImage::thumbnailFileUrl(Yii::getAlias('@webroot/img/no_image.jpg'),$w,$h));
    }

    /**
     * First's image URL
     * @return string
     */
    public function getFirstImageUrl()
    {
        if(empty($this->postImages[0]->file_path) && empty($this->postImages[0]->file_url)){
            return Url::to('@web/img/no_image.jpg');
        }elseif(!empty($this->postImages[0]->file_path)){
            return Url::to('@web/uploads/img/'.$this->postImages[0]->file_path);
        }else{
            return $this->postImages[0]->file_url;
        }
    }

    /**
     * First's image URL (extended functionality, crop, thumbnail ability)
     * @param $w
     * @param $h
     * @param bool $watermark
     * @param bool $placeholder
     * @return null|string
     */
    public function getFirstImageUrlEx($w, $h, $watermark = true, $placeholder = true)
    {
        if(empty($this->postImages[0]->file_path) && empty($this->postImages[0]->file_url)){
            return $placeholder ? "http://placehold.it/{$w}x{$h}" : Url::to('@web/img/no_image.jpg');
        }elseif(!empty($this->postImages[0]->file_path)){
            if($w == 0 || $h == 0){
                return Url::to('@web/uploads/img/'.$this->postImages[0]->file_path);
            }else{
                return $this->postImages[0]->need_crop ? $this->postImages[0]->getCroppedUrl($w, $h, $watermark) : $this->postImages[0]->getThumbnailUrl($w, $h);
            }
        }else{
            return $this->postImages[0]->file_url;
        }
    }

    /**
     * Returns url to post
     * @param bool|true $title
     * @param bool|false $abs
     * @return string
     */
    public function getUrl($title = true, $abs = false)
    {
        $slugTitle = $title ? ArrayHelper::getValue($this->trl,'name',$this->name) : null;
        return Url::to(['/main/post', 'id' => $this->id, 'title' => $title ? Help::slug($slugTitle) : null],$abs);
    }

    /**
     * Returns url to post on FB
     * @return null|string
     */
    public function getFbUrl()
    {
        if(empty($this->fb_sync_id)){
            return null;
        }

        $groupId = $this->group->fb_sync_id;
        $fbId = $this->fb_sync_id;
        return "https://www.facebook.com/groups/{$groupId}/permalink/{$fbId}/";
    }

    /**
     * Returns nested ordered comments
     * @param bool|false $justEnabled
     * @return array|\yii\db\ActiveRecord[]|Comment[]
     */
    public function getNestedOrderedComments($justEnabled = false)
    {
        $q = Comment::findBySql('SELECT * FROM '.Comment::tableName().' WHERE post_id = :post ORDER BY IF(answer_to_id, answer_to_id, id), answer_to_id, created_at ASC',['post' => $this->id]);
        if($justEnabled) $q->where(['status_id' => Constants::STATUS_ENABLED]);
        $q->with('children');
        $comments = $q->all();

        return $comments;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return parent::getComments()->orderBy('created_at ASC');
    }

    /**
     * Counts comments
     * @return int|string
     */
    public function countComments()
    {
        return Comment::find()->where(['post_id' => $this->id])->count();
    }

    /**
     * Returns recursively sorted comments
     * @return Comment[]
     */
    public function getCommentsRecursive()
    {
        /* @var $result Comment[] */
        /* @var $temp Comment[] */
        $result = [];
        $temp = Comment::find()
            ->where('answer_to_id IS NULL OR answer_to_id = 0')
            ->andWhere(['post_id' => $this->id])
            ->with([
                'author',
                'children',
                'children.author',
                'children.parent',
                'children.children.author',
                'children.children.parent',
                'parent',
                'parent.author',
                'parent.children',
                'parent.parent.author',
                'parent.parent.children'
            ])
            ->orderBy('created_at ASC')
            ->all();

        foreach($temp as $comment){
            $result[] = $comment;
            $result = ArrayHelper::merge($result,$comment->getRecursiveChildren());
        }

        return $result;
    }

    /**
     * Updating search keywords
     */
    public function updateSearchKeywords()
    {
        //set encoding
        mb_internal_encoding("UTF-8");

        /* @var $languages Language[] */
        $languages = Language::find()->all();

        $words = "";

        //if languages not empty (for confidence)
        if(!empty($languages)) {
            foreach ($languages as $lng) {

                //get TRL object
                $postTrl = $this->getATrl($lng->prefix);

                //append content text
                $words .= !empty($postTrl->name) ? mb_strtolower($postTrl->name) : '';
                $words .= !empty($postTrl->text) ? ' '.mb_strtolower(strip_tags($postTrl->text)) : '';
                $words .= !empty($postTrl->small_text) ? ' '.mb_strtolower(strip_tags($postTrl->small_text)) : '';

                //append category text
                if(!empty($this->categories)){
                    foreach ($this->categories as $cat){
                        $catTrl = $cat->getATrl($lng->prefix);
                        $words .= ' '.!empty($catTrl->name) ? ' '.mb_strtolower($postTrl->name) : '';
                    }
                }
            }
        }

        if(!empty($this->comments)) {
            foreach ($this->comments as $comment) {
                $words .= !empty($comment->text) ? ' '.mb_strtolower($comment->text) : '';
            }
        }

        if(!empty($this->author)) {
            $words .= !empty($this->author->name) ? ' '.$this->author->name.' '.$this->author->surname : '';
        }

        $this->search_keywords = $words;
        $this->update();
    }


    /**
     * Builds complexly sort query for selecting all posts depending on current categories
     * @param null|int $curCatId
     * @param null|int[] $curCatIds
     * @param null|int[] $sibIds
     * @param bool $sticky
     * @return ActiveQuery
     */
    public static function findSorted($curCatId = null, $curCatIds = null, $sibIds = null, $sticky = true)
    {

        //implode for using in queries
        $currentIdsStr = !empty($curCatIds) ? implode(',',$curCatIds) : null;
        $siblingIdsStr = !empty($sibIds) ? implode(',',$sibIds) : null;

        //get all basic posts data
        /* @var $posts Post[] */
        $mainPostsQuery = Post::find()
            ->alias('p')
            ->joinWith('postCategories as pc');

        //build ordering condition
        $orderPriorities = [];
        if(!empty($curCatId)){
            if($sticky) : $orderPriorities[] = "IF((pc.category_id = :cat AND sticky_position > 0), sticky_position, 2147483647) ASC"; endif;
//            $orderPriorities[] = "IF(pc.category_id = :cat, 0, 2147483647) ASC";
            $orderPriorities[] = "IF(pc.category_id IN ({$currentIdsStr}), 0, 2147483647) ASC";
            if(!empty($sibIds)) : $orderPriorities[] = "IF(pc.category_id IN ({$siblingIdsStr}), 0, 2147483647) ASC"; endif;
        }elseif($sticky){
            $orderPriorities[] = "IF(sticky_position_main, sticky_position_main, 2147483647)";
        }
        $orderPriorities[] = "IF(content_type_id = :lowestPriorityType, 2147483647, 0) ASC";
        $orderPriorities[] = "p.published_at DESC";


        //finalize query
        $orderParams = ['lowestPriorityType' => Constants::CONTENT_TYPE_POST];
        if(!empty($curCatId)) : $orderParams['cat'] = $curCatId; endif;
        $mainPostsQuery->orderBy(new Expression(implode(', ',$orderPriorities), $orderParams));

        return $mainPostsQuery;
    }

    /**
     * Builds complexly sort query for selecting all popular posts depending on current categories
     * @param null $curCatId
     * @param null $curCatIds
     * @param null $sibIds
     * @return ActiveQuery
     */
    public static function findSortedPopular($curCatId = null, $curCatIds = null, $sibIds = null)
    {
        //implode for using in queries
        $currentIdsStr = implode(',',$curCatIds);
        $siblingIdsStr = implode(',',$sibIds);

        //get all basic posts data
        /* @var $posts Post[] */
        $mainPostsQuery = Post::find()
            ->alias('p')
            ->joinWith('postCategories as pc');

        //build ordering condition
        $orderPriorities = [];
        if(!empty($curCatId)){
            $orderPriorities[] = "IF(pc.category_id IN ({$currentIdsStr}), 0, 2147483647) ASC";
            if(!empty($sibIds)) : $orderPriorities[] = "IF(pc.category_id IN ({$siblingIdsStr}), 0, 2147483647) ASC"; endif;
        }
        $orderPriorities[] = "p.comment_count DESC";

        //finalize query
        $mainPostsQuery->orderBy(new Expression(implode(', ',$orderPriorities)));

        return $mainPostsQuery;
    }
}