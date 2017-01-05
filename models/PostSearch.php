<?php

namespace app\models;

use app\helpers\Constants;
use Yii;
use yii\data\ActiveDataProvider;

class PostSearch extends Post
{

    public $internal_name, $name, $content;

    /**
     * Validation rules for search
     * @return array
     */
    public function rules()
    {
        return [
            [['internal_name', 'name', 'content'], 'string', 'max' => 255],
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
            'internal_name' => Yii::t('admin','Internal name'),
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
        $q = parent::find()->where('status_id != :st', ['st' => Constants::STATUS_IN_STOCK]);

        $this->load($params);

        if($this->validate()){

            if(!empty($this->internal_name)){
                $q->andWhere(['like','name', $this->internal_name]);
            }

            if(!empty($this->content_type_id)){
                $q->andWhere(['content_type_id' => $this->content_type_id]);
            }

            if(!empty($this->type_id)){
                $q->andWhere(['type_id' => $this->content_type_id]);
            }

            if(!empty($this->name)){
                $q->joinWith('postTrls as trl')->andWhere(['lng' => $lng, 'trl.name' => $this->name]);
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