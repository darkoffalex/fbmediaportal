<?php

namespace app\modules\admin\controllers;

use app\helpers\Help;
use app\models\CommonSettings;
use yii\base\Model;
use yii\helpers\Url;
use yii\web\UploadedFile;
use yii\web\Controller;
use Yii;

class CommonSettingsController extends Controller
{
    /**
     * Common settings
     * @return string
     */
    public function actionIndex()
    {
        /* @var $model CommonSettings */
        $model = CommonSettings::find()->one();

        if(empty($model)){
            $model = new CommonSettings();
        }

        if(Yii::$app->request->isPost && $model->load(Yii::$app->request->post())){
            $model->image = UploadedFile::getInstance($model,'image');

            if(!empty($model->image) && $model->image->size > 0){
                //generate name
                $filename = Yii::$app->security->generateRandomString(16).'.'.$model->image->extension;

                //try save to dir
                try{
                    //delete old file
                    $model->deleteFile();

                    //save new file and set filename
                    if($model->image->saveAs(Yii::getAlias('@webroot/uploads/img/'.$filename))){
                        $model->header_logo_filename = $filename;
                    }
                }catch (\Exception $ex){
                    Help::log('upload_errors.txt',$ex->getMessage());
                    $model->addError('image','Error while saving file');
                }
            }

            $model->image = null;
            $model->isNewRecord ? $model->save() : $model->update();
            Yii::$app->cache->flush();
        }

        return $this->render('index',compact('model'));
    }

    /**
     * Delete logo
     * @return \yii\web\Response
     */
    public function actionDelLogo()
    {
        /* @var $model CommonSettings */
        $model = CommonSettings::find()->one();

        if(!empty($model)){
            $model->deleteFile();
            $model->header_logo_filename = '';
            $model->update();
            Yii::$app->cache->flush();
        }

        return $this->redirect(Url::to(['/admin/common-settings/index']));
    }
}