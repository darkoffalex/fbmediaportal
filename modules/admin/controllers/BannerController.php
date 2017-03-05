<?php

namespace app\modules\admin\controllers;

use app\helpers\Help;
use app\models\Banner;
use app\models\BannerDisplay;
use app\models\BannerPlace;
use app\models\BannerPlaceDB;
use app\models\BannerPlaceSearch;
use app\models\BannerSearch;
use Yii;
use app\helpers\Sort;
use app\helpers\Constants;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class BannerController extends Controller
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

                    Yii::$app->cache->flush();

                    return $this->redirect(Url::to(['/admin/banner/index']));
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

                    Yii::$app->cache->flush();
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
        Yii::$app->cache->flush();

        return $this->redirect(Yii::$app->request->referrer);
    }


    /******************************************** P L A C E S *********************************************************/

    /**
     * Listing all banners
     * @return string
     */
    function actionPlaces()
    {
        $searchModel = new BannerPlaceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('places', compact('searchModel','dataProvider'));
    }

    /**
     * Creating banner place
     * @return string
     */
    public function actionCreatePlace()
    {
        $model = new BannerPlace();

        //ajax validation
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if(Yii::$app->request->isPost && $model->load(Yii::$app->request->post())){
            if($model->validate()){

                $model->created_at = date('Y-m-d H:i:s', time());
                $model->updated_at = date('Y-m-d H:i:s', time());
                $model->created_by_id = Yii::$app->user->id;
                $model->updated_by_id = Yii::$app->user->id;
                $model->save();

                Yii::$app->cache->flush();

                return $this->redirect(Url::to(['/admin/banner/places']));
            }
        }

        return $this->renderAjax('_edit_place',compact('model'));
    }

    /**
     * Update banner place
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdatePlace($id)
    {
        /* @var $model BannerPlace */
        $model = BannerPlace::findOne((int)$id);

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Banner not found'),404);
        }

        //ajax validation
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if(Yii::$app->request->isPost && $model->load(Yii::$app->request->post())){
            if($model->validate()){

                $model->updated_at = date('Y-m-d H:i:s', time());
                $model->updated_by_id = Yii::$app->user->id;
                $model->update();

                Yii::$app->cache->flush();

                return $this->redirect(Url::to(['/admin/banner/places']));
            }
        }

        return $this->renderAjax('_edit_place',compact('model'));
    }

    /**
     * Deleting banner place
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDeletePlace($id)
    {
        /* @var $model BannerPlace */
        $model = BannerPlace::findOne((int)$id);

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Banner not found'),404);
        }

        $model->delete();
        Yii::$app->cache->flush();

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Display schedule for banner place (select which banner should be shown at specified time)
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPlaceScheduler($id)
    {
        /* @var $model BannerPlace */
        $model = BannerPlace::findOne((int)$id);

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Banner not found'),404);
        }

        /* @var $banners Banner[] */
        $banners = Banner::find()->all();

        /* @var $calendarConfig array[] config to display on schedule added elements */
        $calendarConfig = [];

        if(!empty($model->bannerDisplays)){
            foreach($model->bannerDisplays as $display){
                $calendarConfig[] = [
                    'item_id' => $display->id,
                    'title' => $display->banner->name,
                    'start' => $display->start_at,
                    'end' => $display->end_at,
                    'backgroundColor' => '#3c8dbc',
                    'borderColor' => '#3c8dbc'
                ];
            }
        }

        $calendarConfig = json_encode($calendarConfig);

        return $this->render('schedule',compact('model','banners','calendarConfig'));
    }

    /**
     * Clears specified place from all banners
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionClearPlace($id)
    {
        /* @var $model BannerPlace */
        $model = BannerPlace::findOne((int)$id);

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Banner not found'),404);
        }

        BannerDisplay::deleteAll(['place_id' => $model->id]);
        Yii::$app->cache->flush();

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Adding new item to schedule
     * @param $id
     * @return array|string
     * @throws NotFoundHttpException
     */
    public function actionAddTime($id)
    {
        /* @var $model BannerPlace */
        $model = BannerPlace::findOne((int)$id);

        if(empty($model)){
            return 'FAILED';
        }

        //If incoming request has ajax type
        if(Yii::$app->request->isAjax){

            //get necessary information
            $bannerId = Yii::$app->request->post('banner_id');
            $startAt = Yii::$app->request->post('start_date');
            $endAt = Yii::$app->request->post('end_date');
            $duration = Yii::$app->request->post('duration_time',3600);

            /* @var $banner Banner */
            $banner = Banner::findOne((int)$bannerId);

            if(!empty($banner)){

                //get start time (timestamp)
                $time_start = !empty($startAt) ? strtotime($startAt) : time();
                //calculate end time (using duration) or get from request if set
                $time_end = !empty($endAt) ? strtotime($endAt) : $time_start + $duration;

                //create schedule item
                $display = new BannerDisplay();
                $display->place_id = $id;
                $display->banner_id = $banner->id;
                $display->start_at = date('Y-m-d H:i:s', $time_start);
                $display->end_at = date('Y-m-d H:i:s', $time_end);

                $display->created_at = date('Y-m-d H:i:s', time());
                $display->updated_at = date('Y-m-d H:i:s', time());
                $display->created_by_id = Yii::$app->user->id;
                $display->updated_by_id = Yii::$app->user->id;
                $display->save();

                Yii::$app->cache->flush();

                //response (json)
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'id' => $display->id,
                    'start_date' => $display->start_at,
                    'end_date' => $display->end_at
                ];
            }
        }

        //failure response
        return 'FAILED';
    }

    /**
     * Editing schedule item
     * @return array|string
     * @throws \Exception
     */
    public function actionEditTime()
    {
        /* @var $model BannerDisplay */
        $display = BannerDisplay::findOne((int)Yii::$app->request->post('id'));

        if(empty($display)){
            return 'FAILED';
        }

        $startAt = Yii::$app->request->post('start_date');
        $endAt = Yii::$app->request->post('end_date');

        $display->start_at = $startAt;
        $display->end_at = $endAt;

        $display->updated_at = date('Y-m-d H:i:s', time());
        $display->updated_by_id = Yii::$app->user->id;
        $display->update();

        Yii::$app->cache->flush();

        //response (json)
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'id' => $display->id,
            'start_date' => $display->start_at,
            'end_date' => $display->end_at
        ];
    }

    /**
     * Delete schedule item
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function actionDeleteTime($id)
    {
        /* @var $model BannerDisplay */
        $display = BannerDisplay::findOne((int)$id);

        if(empty($display)){
            return 'FAILED';
        }

        $deleted = $display->delete();
        Yii::$app->cache->flush();
        return $deleted ? 'OK' : 'FAILED';
    }
}