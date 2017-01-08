<?php

namespace app\models;

use Yii;
use yii\helpers\Url;
use yii\web\UploadedFile;

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
     * @var UploadedFile
     */
    public $image = null;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $baseLabels = parent::attributeLabels();
        $baseLabels['image'] = 'Image file';
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
        $baseRules[] = [['image'], 'file', 'extensions' => ['png', 'jpg', 'gif'], 'maxSize' => 1024*1024];
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
            $trl = new PostImageTrl();
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

    /**
     * @return string
     */
    public function getFullUrl()
    {
        return $this->is_external ? $this->file_url : Url::to('@web/uploads/img/'.$this->file_path);
    }

    /**
     * Checks if file is exist
     * @return bool
     */
    public function hasFile()
    {
        if(empty($this->file_path)){
            return false;
        }
        return file_exists(Yii::getAlias('@webroot/uploads/img/'.$this->file_path));
    }

    /**
     * Deletes uploaded file if exist
     * @return bool
     */
    public function deleteFile()
    {
        if(!$this->hasFile()){
            return false;
        }

        return unlink(Yii::getAlias('@webroot/uploads/img/'.$this->file_path));
    }
}