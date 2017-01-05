<?php

namespace app\modules\admin\controllers;

use app\models\CategoryTrl;
use app\models\CategoryTrlDB;
use app\models\LabelTrl;
use app\models\Language;
use app\models\LanguagesSearch;
use app\models\PostImageTrl;
use app\models\PostTrl;
use app\models\PostVoteAnswerTrl;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class LanguagesController extends Controller
{
    /**
     * Render list of all languages
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new LanguagesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', compact('searchModel','dataProvider'));
    }

    /**
     * Creating a language (modal window)
     * @return array|string|Response
     */
    public function actionCreate()
    {
        $model = new Language();

        //ajax validation
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        //if something coming from POST
        if(Yii::$app->request->isPost){

            //load data
            $model->load(Yii::$app->request->post());;

            //set some statistics info
            $model->created_at = date('Y-m-d H:i:s',time());
            $model->updated_at = date('Y-m-d H:i:s',time());
            $model->created_by_id = Yii::$app->user->id;
            $model->updated_by_id = Yii::$app->user->id;

            //if validated - save and go to list
            if($model->validate()){
                $model->save();
                return $this->redirect(Url::to(['/admin/languages/index']));
            }
        }

        return $this->renderAjax('_edit',compact('model'));
    }

    /**
     * Updating a language (modal window)
     * @param $id
     * @return array|string|Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        /* @var $model Language */
        $model = Language::findOne((int)$id);

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Language not found'),404);
        }

        //ajax validation
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        //if something coming from POST
        if(Yii::$app->request->isPost){

            //load data
            $model->load(Yii::$app->request->post());;

            //set some statistics info
            $model->updated_at = date('Y-m-d H:i:s',time());
            $model->updated_by_id = Yii::$app->user->id;

            //if validated - save and go to list
            if($model->validate()){
                $model->update();
                return $this->redirect(Url::to(['/admin/languages/index']));
            }
        }

        return $this->renderAjax('_edit',compact('model'));
    }

    /**
     * Deleting languages
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        /* @var $model Language */
        $model = Language::findOne((int)$id);

        if(empty($model) || Language::find()->count() < 2){
            throw new NotFoundHttpException(Yii::t('admin','Language not found'),404);
        }

        //TODO: delete all translatable objects
        CategoryTrl::deleteAll(['lng' => $model->prefix]);
        LabelTrl::deleteAll(['lng' => $model->prefix]);
        PostVoteAnswerTrl::deleteAll(['lng' => $model->prefix]);
        PostImageTrl::deleteAll(['lng' => $model->prefix]);
        PostTrl::deleteAll(['lng' => $model->prefix]);

        $model->delete();

        return $this->redirect(Yii::$app->request->referrer);
    }
}