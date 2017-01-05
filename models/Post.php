<?php

namespace app\models;

use Yii;

/**
 * @property PostTrl $trl
 * @property PostTrl $aTrl
 */
class Post extends PostDB
{
    /**
     * @var array for loading translations from POST
     */
    public $translations = [];

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
        $baseRules[] = [['translations'],'safe'];
        return $baseRules;
    }

    /**
     * @param string|null $lng
     * @return PostTrl
     */
    public function getATrl($lng = null)
    {
        $lng = empty($lng) ? Yii::$app->language : $lng;

        /* @var $trl PostTrl */
        $trl = PostTrl::findOne(['post_id' => $this->id, 'lng' => $lng]);

        if(empty($trl)){
            $trl = new LabelTrl();
            $trl -> post_id = $this->id;
            $trl -> lng = $lng;
        }

        return $trl;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrl()
    {
        $lng = Yii::$app->language;
        return $this->hasOne(LabelTrl::className(), ['post_id' => 'id'])->where(['lng' => $lng]);
    }
}