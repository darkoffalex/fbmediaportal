<?php

namespace app\models;

use app\helpers\Constants;
use Yii;
use yii\data\ActiveDataProvider;
use app\models\User;

class PostSearch extends Post
{

    public $content, $category_id;

    /**
     * Validation rules for search
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'content', 'published_at', 'created_at', 'need_finish'], 'string', 'max' => 255],
            [['content_type_id', 'id', 'type_id', 'category_id', 'author_id', 'group_id', 'kind_id'], 'integer'],
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
     * @return ActiveDataProvider
     */
    public function search($params, $lng, $stock = false)
    {
        //all posts that aren't in stock
        $q = parent::find()->where($stock ? 'post.status_id = :st' : 'post.status_id != :st', ['st' => Constants::STATUS_IN_STOCK]);
        $q -> with(['categories','author','comments','group']);

        $this->load($params);

        if($this->validate()){

            if(!empty($this->id)){
                $q->andWhere(['id' => (int)$this->id]);
            }

            if(!empty($this->author_id)){
                $q->andWhere(['post.author_id' => $this->author_id]);
            }

            if(!empty($this->kind_id)){
                $q->andWhere(['post.kind_id' => $this->kind_id]);
            }

            if(!empty($this->category_id)){
                $q->joinWith('categories as cat')->andWhere(['cat.id' => $this->category_id]);
            }

            if(!empty($this->name)){
                $q->andWhere(['like','post.name', $this->name]);
            }

            if(!empty($this->content_type_id)){
                $q->andWhere(['content_type_id' => $this->content_type_id]);
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

            if(!empty($this->content)){
                $q->joinWith('postSearchIndices as psi')->andWhere(['like','psi.text',$this->content]);
                $q->distinct();
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