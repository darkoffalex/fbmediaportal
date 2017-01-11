<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;

class BannerPlaceSearch extends BannerPlace
{

    /**
     * Validation rules for search
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'alias'], 'string', 'max' => 255],
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
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $q = parent::find();

        $this->load($params);

        if($this->validate()){

            if(!empty($this->name)){
                $q->andWhere(['like','name', $this->name]);
            }

            if(!empty($this->alias)){
                $q->andWhere(['like','alias', $this->alias]);
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