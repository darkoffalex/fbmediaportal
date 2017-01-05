<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;

class LabelSearch extends Label
{

    /**
     * Validation rules for search
     * @return array
     */
    public function rules()
    {
        return [
            [['source_word'], 'string', 'max' => 255],
        ];
    }

    /**
     * Build search query and return as result data provider
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $q = parent::find();

        $this->load($params);

        if($this->validate()){

            if(!empty($this->name)){
                $q->andWhere(['like','source_word', $this->name]);
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