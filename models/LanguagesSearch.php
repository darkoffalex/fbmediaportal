<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;

class LanguagesSearch extends Language
{

    /**
     * Validation rules for search
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'self_name'], 'string', 'max' => 255],
            [['prefix'], 'string', 'max' => 5],
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
                $q->andWhere(['like','name', $this->name]);
            }

            if(!empty($this->self_name)){
                $q->andWhere(['like','self_name', $this->self_name]);
            }

            if(!empty($this->prefix)){
                $q->andWhere(['like','prefix', $this->prefix]);
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