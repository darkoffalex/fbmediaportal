<?php

namespace app\models;

use app\helpers\Constants;
use Yii;
use yii\data\ActiveDataProvider;
use app\models\User;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class PostSearch extends Post
{

    public $content, $category_id, $nested;

    /**
     * Validation rules for search
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'content', 'published_at', 'created_at', 'need_finish', 'need_update', 'is_parsed', 'fb_sync_id'], 'string', 'max' => 255],
            [['content_type_id', 'id', 'type_id', 'category_id', 'author_id', 'group_id', 'kind_id', 'status_id', 'nested'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $baseLabels = parent::attributeLabels();

        $newLabels = [
            'content' => Yii::t('admin','Content'),
            'nested' => Yii::t('admin','Nested')
        ];

        return array_merge($baseLabels,$newLabels);
    }

    /**
     * Returns selected author name
     * @return null|string
     */
    public function actionGetAuthorName()
    {
        if(!empty($this->author_id)){
            $user = User::findOne(['id' => $this->author_id]);
            if(!empty($user)){
                return $user->name.' '.$user->surname;
            }
        }

        return null;
    }

    /**
     * Build search query and return as result data provider
     * @param array $params
     * @param string $lng
     * @param bool $stock
     * @param string $stockType
     * @return ActiveDataProvider
     */
    public function search($params, $lng, $stock = false, $stockType = 'main')
    {
        //all posts that aren't in stock and archive
        if(!$stock){
            $q = parent::find()->where('post.status_id != :stock AND post.status_id != :archive', ['stock' => Constants::STATUS_IN_STOCK, 'archive' => Constants::STATUS_DELETED]);
        //only archived or stocked posts
        }else{
            $q = parent::find()->where(['status_id' => $stockType == 'main' ? Constants::STATUS_IN_STOCK : Constants::STATUS_DELETED]);
        }

        $q -> with(['categories','author','comments','group']);

        $this->load($params);

        if($this->validate()){

            if(!empty($this->id)){
                $q->andWhere(['id' => (int)$this->id]);
            }

            if(!empty($this->author_id)){
                $q->andWhere(['post.author_id' => $this->author_id]);
            }

            if(!empty($this->fb_sync_id)){
                $q->andWhere(['fb_sync_id' => $this->fb_sync_id]);
            }

            if(!empty($this->kind_id)){
                if($this->kind_id != Constants::KIND_NOT_SELECTED){
                    $q->andWhere(['post.kind_id' => $this->kind_id]);
                }else{
                    $q->andWhere(new Expression("post.kind_id IS NULL OR post.kind_id = ''"));
                }
            }

            if(!empty($this->category_id)){

                if(!$this->nested){
                    $q->joinWith('categories as cat')->andWhere(['cat.id' => $this->category_id]);
                }else{
                    /* @var $category Category */
                    $category = Category::find()->where(['id' => $this->category_id])->one();
                    //obtain children ID's
                    $children = $category->getChildrenRecursive(true);
                    $currentIds = array_values(ArrayHelper::map($children,'id','id'));
                    //include current id
                    $currentIds[] = $category->id;
                    //query
                    $q->joinWith('categories as cat')->andWhere(['cat.id' => $currentIds]);
                }
            }

            if(!empty($this->name)){
                $q->andWhere(['like','post.name', $this->name]);
            }

            if(!empty($this->content_type_id)){
                $q->andWhere(['content_type_id' => $this->content_type_id]);
            }

            if(is_numeric($this->status_id)){
                $q->andWhere(['post.status_id' => $this->status_id]);
            }

            if(!empty($this->type_id)){
                $q->andWhere(['post.type_id' => $this->type_id]);
            }

            if(!empty($this->group_id)){
                $q->andWhere(['post.group_id' => $this->group_id]);
            }

            if(!empty($this->need_finish)){
                if($this->need_finish == "YES"){
                    $q->andWhere(['post.need_finish' => 1]);
                }else{
                    $q->andWhere('post.need_finish = 0 OR post.need_finish IS NULL');
                }
            }

            if(!empty($this->need_update)){
                if($this->need_update == "YES"){
                    $q->andWhere(['post.need_update' => 1]);
                }else{
                    $q->andWhere('post.need_update = 0 OR post.need_update IS NULL');
                }
            }

            if(!empty($this->is_parsed)){
                if($this->is_parsed == "YES"){
                    $q->andWhere(['post.is_parsed' => 1]);
                }else{
                    $q->andWhere('post.is_parsed = 0 OR post.is_parsed IS NULL');
                }
            }

            if(!empty($this->content)){
                $q->andWhere(['like','search_keywords',$this->content]);
            }

            if(!empty($this->created_at)){
                $range = explode(' - ',$this->created_at);
                $date_from = $range[0];
                $date_to = $range[1];
                $q->andWhere('post.created_at >= :from AND post.created_at <= :to',['from' => $date_from, 'to' => $date_to]);
            }

            if(!empty($this->published_at)){
                $range = explode(' - ',$this->published_at);
                $date_from = $range[0];
                $date_to = $range[1];
                $q->andWhere('post.published_at >= :from AND post.published_at <= :to',['from' => $date_from, 'to' => $date_to]);
            }
        }

        return new ActiveDataProvider([
            'query' => $q,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
    }
}