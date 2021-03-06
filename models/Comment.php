<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property Comment $parent
 * @property Comment $admParent
 * @property Comment[] $children
 * @property Comment[] $admChildren
 */
class Comment extends CommentDB
{

    /**
     * @var bool
     */
    public $isFrontend = false;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $baseLabels = parent::attributeLabels();
        foreach($baseLabels as $attribute => $label){
            $baseLabels[$attribute] = !$this->isFrontend ? Yii::t('admin',$label) : $label;
        }
        return $baseLabels;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $baseRules = parent::rules();
        if($this->isFrontend){
            $baseRules[] = [['text'], 'required'];
        }
        return $baseRules;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Comment::className(),['id' => 'answer_to_id'])->orderBy('created_at ASC');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdmParent()
    {
        return $this->hasOne(Comment::className(),['adm_id' => 'answer_to_adm_id'])->orderBy('created_at ASC');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(Comment::className(),['answer_to_id' => 'id'])->orderBy('created_at ASC');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdmChildren()
    {
        return $this->hasMany(Comment::className(),['answer_to_adm_id' => 'adm_id'])->orderBy('created_at ASC');
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
     * Returns recursively sorted children
     * @return Comment[]|array
     */
    public function getRecursiveChildren()
    {
        $children = $this->children;

        foreach($children as $child){
            if(!empty($child->children)){
                $children = ArrayHelper::merge($children,$this->getRecursiveChildren());
            }
        }

        return $children;
    }

    /**
     * Get recursive listed comments
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