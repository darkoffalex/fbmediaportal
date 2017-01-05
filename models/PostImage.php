<?php

namespace app\models;

use Yii;

/**
 * @property PostImageTrl $trl
 * @property PostImageTrl $aTrl
 */
class PostImage extends PostImageDB
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
     * @return PostImageTrl
     */
    public function getATrl($lng = null)
    {
        $lng = empty($lng) ? Yii::$app->language : $lng;

        /* @var $trl PostImageTrl */
        $trl = PostImageTrl::findOne(['post_image_id' => $this->id, 'lng' => $lng]);

        if(empty($trl)){
            $trl = new LabelTrl();
            $trl -> post_image_id = $this->id;
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
        return $this->hasOne(LabelTrl::className(), ['post_image_id' => 'id'])->where(['lng' => $lng]);
    }
}