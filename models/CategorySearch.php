<?php

namespace app\models;

use app\helpers\Constants;
use Yii;
use yii\data\ActiveDataProvider;
use app\models\User;

class CategorySearch extends Category
{
    /**
     * Validation rules for search
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $baseLabels = parent::attributeLabels();
        return $baseLabels;
    }

    /**
     * Build search query and return as result data provider
     * @param array $params
     * @param int $rootId
     * @return ActiveDataProvider
     */
    public function search($params, $rootId = 0)
    {
        //all posts that aren't in stock
        $q = parent::find()->where(['parent_category_id' => (int)$rootId])->orderBy('priority ASC');
        $q -> with('posts');

        $this->load($params);

        if($this->validate()){


            if(!empty($this->name)){
                $q->andWhere(['like','name', $this->name]);
            }

            if(!empty($this->created_at)){
                $range = explode(' - ',$this->created_at);
                $date_from = $range[0];
                $date_to = $range[1];
                $q->andWhere('created_at >= :from AND created_at <= :to',['from' => $date_from, 'to' => $date_to]);
            }
        }

        return new ActiveDataProvider([
            'query' => $q,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);
    }
}