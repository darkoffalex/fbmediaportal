<?php

namespace app\models;

use app\helpers\Constants;
use app\helpers\Help;
use app\models\Comment;
use himiklab\thumbnail\EasyThumbnailImage;
use Yii;
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $baseLabels = parent::attributeLabels();
        foreach($baseLabels as $attribute => $label){
            $baseLabels[$attribute] = Yii::t('admin',$label);
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
     * @return string
     */
    public function getThumbnailUrl($w = 90, $h = 70)
    {
        if(empty($this->postImages[0]->file_path) && empty($this->postImages[0]->file_url)){
            return EasyThumbnailImage::thumbnailFileUrl(Yii::getAlias('@webroot/img/no_image.jpg'),$w,$h);
        }elseif(!empty($this->postImages[0]->file_path)){
            return EasyThumbnailImage::thumbnailFileUrl(Yii::getAlias('@webroot/uploads/img/'.$this->postImages[0]->file_path),$w,$h);
        }else{
            return $this->postImages[0]->file_url;
//            return EasyThumbnailImage::thumbnailFileUrl($this->postImages[0]->file_url,$w,$h);
        }
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
     * Returns url to post
     * @param bool|true $title
     * @param bool|false $abs
     * @return string
     */
    public function getUrl($title = true, $abs = false)
    {
        $slugTitle = $title ? ArrayHelper::getValue($this->trl,'name',$this->name) : null;
        return Url::to(['/posts/show', 'id' => $this->id, 'title' => $title ? Help::slug($slugTitle) : null],$abs);
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
     * Updating indexes
     * @param array $type
     * @return bool
     * @throws \yii\db\Exception
     */
    public function updateSearchIndices($type = [Constants::IND_R_ALL])
    {
        if(!is_array($type) || empty($type)){
            return false;
        }

        if(in_array(Constants::IND_R_ALL,$type)){
            PostSearchIndex::deleteAll(['post_id' => $this->id]);
        }else{
            PostSearchIndex::deleteAll(['type_id' => $type, 'post_id' => $this->id]);
        }

        $preparedValues = [];

        /* @var $languages Language[] */
        $languages = Language::find()->all();

        //if languages not empty (for confidence)
        if(!empty($languages)){
            foreach($languages as $lng){

                //get TRL object
                $postTrl = $this->getATrl($lng->prefix);

                if(in_array(Constants::IND_R_ALL,$type) || in_array(Constants::IND_R_CONTENT,$type)){
                    if(!empty($postTrl->name)){
                        $preparedValues[] = [
                            $postTrl->name,
                            $this->id,
                            Constants::IND_R_CONTENT
                        ];
                    }

                    if(!empty($postTrl->small_text)){
                        $preparedValues[] = [
                            $postTrl->small_text,
                            $this->id,
                            Constants::IND_R_CONTENT
                        ];
                    }

                    if(!empty($postTrl->text)){
                        $preparedValues[] = [
                            strip_tags($postTrl->text),
                            $this->id,
                            Constants::IND_R_CONTENT
                        ];
                    }

                    if(!empty($postTrl->question)){
                        $preparedValues[] = [
                            $postTrl->question,
                            $this->id,
                            Constants::IND_R_CONTENT
                        ];
                    }
                }

                if(in_array(Constants::IND_R_ALL,$type) || in_array(Constants::IND_R_IMAGES,$type)){
                    if(!empty($this->postImages)){
                        foreach($this->postImages as $img){
                            $imgTrl = $img->getATrl($lng->prefix);

                            if(!empty($imgTrl->name)){
                                $preparedValues[] = [
                                    $imgTrl->name,
                                    $this->id,
                                    Constants::IND_R_IMAGES
                                ];
                            }

                            if(!empty($imgTrl->signature)){
                                $preparedValues[] = [
                                    $imgTrl->signature,
                                    $this->id,
                                    Constants::IND_R_IMAGES
                                ];
                            }
                        }
                    }
                }

                if(in_array(Constants::IND_R_ALL,$type) || in_array(Constants::IND_R_ANSWERS,$type)){
                    if(!empty($this->postVoteAnswers)){
                        foreach($this->postVoteAnswers as $answer){
                            $answerTrl = $answer->getATrl($lng->prefix);

                            if(!empty($answerTrl->text)){
                                $preparedValues[] = [
                                    $answerTrl->text,
                                    $this->id,
                                    Constants::IND_R_ANSWERS
                                ];
                            }
                        }
                    }
                }

                if(in_array(Constants::IND_R_ALL,$type) || in_array(Constants::IND_R_CATEGORIES,$type)){
                    if(!empty($this->categories)){
                        foreach($this->categories as $cat){
                            $catTrl = $cat->getATrl($lng->prefix);

                            if(!empty($catTrl->name)){
                                $preparedValues[] = [
                                    $catTrl->name,
                                    $this->id,
                                    Constants::IND_R_CATEGORIES
                                ];
                            }
                        }
                    }
                }

                if(in_array(Constants::IND_R_ALL,$type) || in_array(Constants::IND_R_COMMENTS,$type)){
                    if(!empty($this->comments)){
                        foreach($this->comments as $comment){

                            if(!empty($comment->text)){
                                $preparedValues[] = [
                                    $comment->text,
                                    $this->id,
                                    Constants::IND_R_COMMENTS
                                ];
                            }
                        }
                    }
                }
            }
        }

        $affectedRows = 0;
        if(!empty($preparedValues)){
            $affectedRows = Yii::$app->db->createCommand()->batchInsert(PostSearchIndex::tableName(), ['text', 'post_id', 'type_id'],$preparedValues)->execute();
        }

        return !empty($affectedRows);
    }
}