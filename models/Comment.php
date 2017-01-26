<?php

namespace app\models;

use Yii;

/**
 * @property Comment $parent
 * @property Comment $fbParent
 * @property Comment[] $children
 * @property Comment[] $fbChildren
 */
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Category::className(),['id' => 'answer_to_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFbParent()
    {
        return $this->hasOne(Category::className(),['fb_sync_id' => 'answer_to_fb_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(Category::className(),['answer_to_id' => 'id'])->orderBy('created_at ASC');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFbChildren()
    {
        return $this->hasMany(Category::className(),['answer_to_fb_id' => 'fb_sync_id'])->orderBy('created_at ASC');
    }


    /**
     * Recursively deletes all sub-comments and comment itself
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
     * Get recursive listed categories
     * @param int $rootId
     * @param int $postId
     * @return Comment[]
     */
    public static function getRecursiveItems($postId, $rootId = 0)
    {
        /* @var $result self[] */
        $result = [];

        /* @var $items self[] */
        $q = self::find()->where(['post_id' => (int)$postId])->orderBy('created_at ASC')->where(['answer_to_id' => $rootId]);
        $items = $q->all();

        foreach($items as $category){
            $result[] = $category;

            if(!empty($category->children)){
                $result = array_merge($result,self::getRecursiveItems($postId,$category->id));
            }
        }

        return $result;
    }
}