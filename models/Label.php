<?php

namespace app\models;

use Yii;

/**
 * @property LabelTrl $trl
 * @property LabelTrl $aTrl
 */
class Label extends LabelDB
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
     * @return LabelTrl
     */
    public function getATrl($lng = null)
    {
        $lng = empty($lng) ? Yii::$app->language : $lng;

        /* @var $trl LabelTrl */
        $trl = LabelTrl::findOne(['label_id' => $this->id, 'lng' => $lng]);

        if(empty($trl)){
            $trl = new LabelTrl();
            $trl -> label_id = $this->id;
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
        return $this->hasOne(LabelTrl::className(), ['label_id' => 'id'])->where(['lng' => $lng]);
    }
}