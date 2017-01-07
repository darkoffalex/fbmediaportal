<?php

namespace app\models;

use app\helpers\Constants;
use Yii;
use yii\data\ActiveDataProvider;

class PostSearch extends Post
{

    public $trl_name, $content;

    /**
     * Validation rules for search
     * @return array
     */
    public function rules()
    {
        return [
            [['trl_name', 'name', 'content', 'author_id'], 'string', 'max' => 255],
            [['content_type_id', 'type_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $baseLabels = parent::attributeLabels();

        $newLabels = [
            'trl_name' => Yii::t('admin','Name'),
            'content' => Yii::t('admin','Content'),
        ];

        return array_merge($baseLabels,$newLabels);
    }

    /**
     * Build search query and return as result data provider
     * @param array $params
     * @param string $lng
     * @return ActiveDataProvider
     */
    public function search($params, $lng)
    {
        //all posts that aren't in stock
        $q = parent::find()->where('post.status_id != :st', ['st' => Constants::STATUS_IN_STOCK]);

        $this->load($params);

        if($this->validate()){

            if(!empty($this->author_id)){
                $q->joinWith('author aut');

                $words = explode(' ',$this->author_id,2);

                if(count($words) > 1){
                    $q->andWhere(['like','aut.name',$words[0]])
                        ->andWhere(['like','aut.surname',$words[1]]);
                }else{
                    $q->andWhere(['like','aut.name', $this->author_id])
                        ->orWhere(['like','aut.surname',$this->author_id]);
//                        ->orWhere(['like','aut.username', $q])
                }

                $q->orWhere(['like','author_custom_name',$this->author_id]);
            }

            if(!empty($this->name)){
                $q->andWhere(['like','name', $this->name]);
            }

            if(!empty($this->content_type_id)){
                $q->andWhere(['content_type_id' => $this->content_type_id]);
            }

            if(!empty($this->type_id)){
                $q->andWhere(['type_id' => $this->content_type_id]);
            }

            if(!empty($this->trl_name)){
                $q->joinWith('postTrls as trl')->andWhere(['lng' => $lng, 'trl.name' => $this->trl_name]);
            }
        }

        return new ActiveDataProvider([
            'query' => $q,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
    }
}