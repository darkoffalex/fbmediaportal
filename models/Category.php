<?php

namespace app\models;

use Yii;
/**
 * @property CategoryTrl $trl
 * @property CategoryTrl $aTrl
 * @property Category $parent
 * @property Category[] $children
 */
class Category extends CategoryDB
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
        $baseRules[] = [['name'],'required'];
        return $baseRules;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Category::className(),['id' => 'parent_category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(Category::className(),['parent_category_id' => 'id']);
    }

    /**
     * @return CategoryTrl
     */
    public function getATrl()
    {
        $lng = Yii::$app->language;
        /* @var $trl CategoryTrl */
        $trl = CategoryTrl::findOne(['category_id' => $this->id, 'lng' => $lng]);
        return !empty($trl) ? $trl : new CategoryTrl();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrl()
    {
        $lng = Yii::$app->language;
        return $this->hasOne(CategoryTrl::className(), ['category_id' => 'id'])->where(['lng' => $lng]);
    }
}