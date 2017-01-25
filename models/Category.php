<?php

namespace app\models;

use app\helpers\Constants;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property CategoryTrl $trl
 * @property CategoryTrl $aTrl
 * @property Category $parent
 * @property Category[] $children
 * @property User $createdBy
 * @property User $updatedBy
 */
class Category extends CategoryDB
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
        $baseRules[] = [['name'],'required'];
        $baseRules[] = [['translations'],'safe'];
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
        return $this->hasMany(Category::className(),['parent_category_id' => 'id'])->orderBy('priority ASC');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(),['id' => 'created_by_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(),['id' => 'updated_by_id']);
    }

    /**
     * @param string|null $lng
     * @return CategoryTrl
     */
    public function getATrl($lng = null)
    {
        $lng = empty($lng) ? Yii::$app->language : $lng;

        /* @var $trl CategoryTrl */
        $trl = CategoryTrl::findOne(['category_id' => $this->id, 'lng' => $lng]);

        if(empty($trl)){
            $trl = new CategoryTrl();
            $trl -> category_id = $this->id;
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
        return $this->hasOne(CategoryTrl::className(), ['category_id' => 'id'])->where(['lng' => $lng]);
    }


    /**
     * Recursively deletes all sub-categories and category itself
     * @throws \Exception
     */
    public function recursiveDelete()
    {
        if(count($this->children) > 0){
            foreach($this->children as $child){
                $child->recursiveDelete();
            }
        }

        $this->delete();
    }

    /**
     * Returns nesting depth level
     * @return int
     */
    public function getDepth()
    {
        return count($this->getBreadCrumbs(false));
    }

    /**
     * Checks if this item is last on it's level
     * @return bool
     */
    public function isLast()
    {
        $last = !empty($this->parent->children) ? $this->parent->children[count($this->parent->children)-1] : $this;
        return $last->id == $this->id;
    }

    /**
     * Get bread crumbs pairs
     * @param string $attributeName
     * @param bool|true $useTrl
     * @return array
     */
    public function getBreadCrumbs($useTrl = true, $attributeName = 'name')
    {
        $result = [];

        $currentItem = $this;

        $result[$this->id] = $useTrl ? ArrayHelper::getValue($this->trl,$attributeName) : $this->getAttribute($attributeName);

        while(!empty($currentItem->parent)){
            $result[$currentItem->parent_category_id] = $useTrl ? ArrayHelper::getValue($currentItem->parent->trl,$attributeName) : $currentItem->parent->getAttribute($attributeName);
            $currentItem = $currentItem->parent;
        };

        return array_reverse($result,true);
    }

    /**
     * Get recursive listed categories
     * @param int $rootId
     * @param bool|false $justEnabled
     * @return Category[]
     */
    public static function getRecursiveItems($rootId = 0, $justEnabled = false)
    {
        /* @var $result self[] */
        $result = [];

        /* @var $items self[] */
        $q = self::find()->orderBy('priority ASC')->where(['parent_category_id' => $rootId]);
        if($justEnabled) $q->where(['status_id' => Constants::STATUS_ENABLED]);
        $items = $q->all();

        foreach($items as $category){
            $result[] = $category;

            if(!empty($category->children)){
                $result = array_merge($result,self::getRecursiveItems($category->id,$justEnabled));
            }
        }

        return $result;
    }

    /**
     * Build a recursive array for drop-down controls in forms
     * @param int $rootId
     * @param bool|false $justEnabled
     * @return Category[]
     */
    public static function buildRecursiveArrayForDropDown($rootId = 0, $justEnabled = false)
    {
        /* @var $result self[] */
        $result = [];

        /* @var $items self[] */
        $q = self::find()->orderBy('priority ASC')->where(['parent_category_id' => $rootId]);
        if($justEnabled) $q->where(['status_id' => Constants::STATUS_ENABLED]);
        $items = $q->all();

        foreach($items as $category){
            if(empty($category->children)){
                $tmp = [
                    'label' => $category->name,
                    'url' => '#',
                    'options' => [
                        'data-category-name' => $category->name,
                        'data-category-add' => $category->id,
                        'data-category-remove' => $category->parent_category_id,
                        'data-no-click' => 'true'
                    ]
                ];
            }else{
                $tmp = [
                    'label' => $category->name,
                    'options' => [
                        'data-category-name' => $category->name,
                        'data-category-add' => $category->id,
                        'data-category-remove' => $category->parent_category_id
                    ],
                    'items' => self::buildRecursiveArrayForDropDown($category->id,$justEnabled)
                ];
            }

            $result[] = $tmp;
        }

        return $result;
    }
}