<?php

namespace app\models;

use Yii;

/**
 * @property PostVoteAnswerTrl $trl
 * @property PostVoteAnswerTrl $aTrl
 */
class PostVoteAnswer extends PostVoteAnswerDB
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
     * @return PostVoteAnswerTrl
     */
    public function getATrl($lng = null)
    {
        $lng = empty($lng) ? Yii::$app->language : $lng;

        /* @var $trl PostVoteAnswerTrl */
        $trl = PostVoteAnswerTrl::findOne(['answer_id' => $this->id, 'lng' => $lng]);

        if(empty($trl)){
            $trl = new PostVoteAnswerTrl();
            $trl -> answer_id = $this->id;
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
        return $this->hasOne(PostVoteAnswerTrl::className(), ['answer_id' => 'id'])->where(['lng' => $lng]);
    }

    /**
     * Get percentage of votes
     * @return int
     */
    public function getPercentage()
    {
        $siblingAnswers = $this->post->postVoteAnswers;

        $total = 0;

        foreach($siblingAnswers as $answer){
            $total += $answer->voted_qnt;
        }

        return $total > 0 ? (int)(($this->voted_qnt/$total)*100) : 0;
    }
}