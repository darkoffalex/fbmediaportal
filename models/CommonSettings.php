<?php

namespace app\models;

use Yii;
use yii\helpers\Url;
use yii\web\UploadedFile;

class CommonSettings extends CommonSettingsDB
{

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
        return $baseRules;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return Url::to('@web/uploads/img/'.$this->header_logo_filename);
    }

    /**
     * Checks if file is exist
     * @return bool
     */
    public function hasFile()
    {
        if(empty($this->header_logo_filename)){
            return false;
        }
        return file_exists(Yii::getAlias('@webroot/uploads/img/'.$this->header_logo_filename));
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

        return unlink(Yii::getAlias('@webroot/uploads/img/'.$this->header_logo_filename));
    }
}