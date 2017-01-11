<?php

namespace app\modules\admin\controllers;

use app\helpers\Help;
use app\models\Banner;
use app\models\BannerSearch;
use Yii;
use app\helpers\Sort;
use app\helpers\Constants;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class BannersController extends Controller
{
    /**
     * Listing all banners
     * @return string
     */
    function actionIndex()
    {
        $searchModel = new BannerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', compact('searchModel','dataProvider'));
    }

    /**
     * Creating banner
     * @return string
     */
    public function actionCreate()
    {
        $model = new Banner();

        if(Yii::$app->request->isPost && $model->load(Yii::$app->request->post())){
            $model->image = UploadedFile::getInstance($model,'image');

            if($model->validate()){

                if($model->type_id == Constants::BANNER_TYPE_IMAGE){
                    if(!empty($model->image) && $model->image->size > 0){

                        //generate name
                        $filename = Yii::$app->security->generateRandomString(16).'.'.$model->image->extension;

                        //try save to dir
                        try{
                            //delete old file
                            $model->deleteFile();

                            //save new file and set filename
                            if($model->image->saveAs(Yii::getAlias('@webroot/uploads/img/'.$filename))){
                                $model->image_filename = $filename;
                            }
                        }catch (\Exception $ex){
                            Help::log('upload_errors.txt',$ex->getMessage());
                            $model->addError('image','Error while saving file');
                        }
                    }else{
                        $model->addError('image',Yii::t('admin','Please select file'));
                    }
                }

                if(!$model->hasErrors()){
                    $model->created_at = date('Y-m-d H:i:s', time());
                    $model->updated_at = date('Y-m-d H:i:s', time());
                    $model->created_by_id = Yii::$app->user->id;
                    $model->updated_by_id = Yii::$app->user->id;
                    $model->image = null;
                    $model->save();

                    return $this->redirect(Url::to(['/admin/banners/index']));
                }
            }
        }

        return $this->render('edit',compact('model'));
    }

    /**
     * Update banner
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        /* @var $model Banner */
        $model = Banner::findOne((int)$id);

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Banner not found'),404);
        }

        if(Yii::$app->request->isPost && $model->load(Yii::$app->request->post())){
            $model->image = UploadedFile::getInstance($model,'image');

            if($model->validate()){

                if($model->type_id == Constants::BANNER_TYPE_IMAGE){
                    if(!empty($model->image) && $model->image->size > 0){

                        //generate name
                        $filename = Yii::$app->security->generateRandomString(16).'.'.$model->image->extension;

                        //try save to dir
                        try{
                            //delete old file
                            $model->deleteFile();

                            //save new file and set filename
                            if($model->image->saveAs(Yii::getAlias('@webroot/uploads/img/'.$filename))){
                                $model->image_filename = $filename;
                            }
                        }catch (\Exception $ex){
                            Help::log('upload_errors.txt',$ex->getMessage());
                            $model->addError('image','Error while saving file');
                        }
                    }else{
                        if(!$model->hasFile()){
                            $model->addError('image',Yii::t('admin','Please select file'));
                        }
                    }
                }else{
                    $model->deleteFile();
                    $model->image_filename = '';
                }

                if(!$model->hasErrors()){
                    $model->updated_at = date('Y-m-d H:i:s', time());
                    $model->updated_by_id = Yii::$app->user->id;
                    $model->image = null;
                    $model->update();
                }
            }
        }

        return $this->render('edit',compact('model'));
    }

    /**
     * Deleting banner
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        /* @var $model Banner */
        $model = Banner::findOne((int)$id);

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Banner not found'),404);
        }

        $model->deleteFile();
        $model->delete();

        return $this->redirect(Yii::$app->request->referrer);
    }


    public function actionPlaces()
    {
        return $this->renderContent('There will be list of places');
    }
}