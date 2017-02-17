<?php

namespace app\models;

use app\helpers\Help;
use himiklab\thumbnail\EasyThumbnailImage;
use Imagine\Filter\Basic\Thumbnail;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Point;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\imagine\BaseImage;
use yii\imagine\Image;
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
        $baseRules[] = [['image'], 'file', 'extensions' => ['png', 'jpg', 'gif'], 'maxSize' => 1024*1024*5];
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
        return $this->hasOne(PostImageTrl::className(), ['post_image_id' => 'id'])->where(['lng' => $lng]);
    }

    /**
     * @return string
     */
    public function getFullUrl()
    {
        return $this->is_external ? $this->file_url : Url::to('@web/uploads/img/'.$this->file_path);
    }

    /**
     * Returns url to thumbnail
     * @param $w
     * @param $h
     * @return string
     */
    public function getThumbnailUrl($w, $h)
    {
        if(empty($this->file_path) && empty($this->file_url)){
            return EasyThumbnailImage::thumbnailFileUrl(Yii::getAlias('@webroot/img/no_image.jpg'),$w,$h);
        }elseif(!empty($this->file_path)){
            return EasyThumbnailImage::thumbnailFileUrl(Yii::getAlias('@webroot/uploads/img/'.$this->file_path),$w,$h);
        }else{
            return $this->file_url;
        }
    }

    /**
     * Returns URL to cropped image
     * @param int $w default width
     * @param int $h default height
     * @param bool $useWatermark add watermark
     * @return null|string
     */
    public function getCroppedUrl($w = 706, $h = 311, $useWatermark = true)
    {
        //if nothing to crop - return null
        if(empty($this->file_path) || !$this->hasFile()){
            return null;
        }

        //get crop settings
        $cropSettings = json_decode($this->crop_settings,true);

        //if not found cropped version of file
        if(!file_exists(Yii::getAlias('@webroot/assets/cropped/'.$this->file_path))) {

            //original file
            $imageUploaded = Image::getImagine()->open(Yii::getAlias('@webroot/uploads/img/'.$this->file_path));

            //create dir if not exist
            FileHelper::createDirectory(Yii::getAlias('@webroot/assets/cropped'));

            //if cropping not set
            if(empty($this->crop_settings)){
//                $imageUploaded->thumbnail(new Box($w,$h),ManipulatorInterface::THUMBNAIL_OUTBOUND);
                $imageUploaded = Image::thumbnail($imageUploaded,$w,$h,ManipulatorInterface::THUMBNAIL_OUTBOUND);
            }else{
                $cropSettings['x'] = $cropSettings['x'] < 0 ? 0 : $cropSettings['x'];
                $cropSettings['y'] = $cropSettings['y'] < 0 ? 0 : $cropSettings['y'];
                $cropSettings['w'] = $cropSettings['w'] <= 0 ? $w : $cropSettings['w'];
                $cropSettings['h'] = $cropSettings['h'] <= 0 ? $h : $cropSettings['h'];

                $imageUploaded->crop(new Point($cropSettings['x'],$cropSettings['y']),new Box($cropSettings['w'],$cropSettings['h']));

                if($this->strict_ratio){
                    $imageUploaded->resize(new Box(706,311));
                }else{
                    if($imageUploaded->getSize()->getWidth() > 706){
                        $newW = 706;
                        $newH = (706/$imageUploaded->getSize()->getWidth())*$imageUploaded->getSize()->getHeight();
                        $imageUploaded->resize(new Box($newW,$newH));
                    }
                }
            }

            if($useWatermark){
                try{
                    //watermark
                    $watermark = Image::getImagine()->open(Yii::getAlias('@webroot/img/logo_wm.png'));
                    //corner position
                    $bottomRight = new Point($imageUploaded->getSize()->getWidth() - $watermark->getSize()->getWidth(),
                        $imageUploaded->getSize()->getHeight() - $watermark->getSize()->getHeight());
                    //apply watermark
                    $imageUploaded = BaseImage::watermark($imageUploaded,$watermark,[$bottomRight->getX()-5,$bottomRight->getY()-5]);
                }catch (\Exception $ex){
                    Help::log("watermark.log",$ex->getMessage());
                }
            }

            $imageUploaded->save(Yii::getAlias('@webroot/assets/cropped/'.$this->file_path),['quality' => 100]);
        }

        return Url::to('@web/assets/cropped/'.$this->file_path);
    }

    /**
     * Clears cropped file (removes it)
     * @return bool|null
     */
    public function clearCropped()
    {
        //if nothing to crop - return null
        if(empty($this->file_path) || !$this->hasFile()){
            return null;
        }

        return unlink(Yii::getAlias('@webroot/assets/cropped/'.$this->file_path));
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