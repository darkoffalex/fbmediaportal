<?php

namespace app\models;

use Yii;

/**
 * @property Banner[] $banners
 */
class BannerPlace extends BannerPlaceDB
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBanners()
    {
        return $this->hasMany(Banner::className(), ['id' => 'banner_id'])->viaTable('banner_display', ['place_id' => 'id']);
    }
}