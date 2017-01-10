<?php

namespace app\models;

use Yii;

class Comment extends CommentDB
{

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
        return $baseRules;
    }
}