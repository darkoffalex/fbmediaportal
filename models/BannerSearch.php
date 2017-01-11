<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;

class BannerSearch extends Banner
{

    /**
     * Validation rules for search
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
            [['type_id'], 'integer'],
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

            if(!empty($this->type_id)){
                $q->andWhere(['type_id' => $this->type_id]);
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