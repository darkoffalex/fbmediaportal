<?php

namespace app\modules\admin\controllers;

use app\helpers\Help;
use app\models\CategoryTrl;
use app\models\Label;
use app\models\LabelSearch;
use app\models\LabelTrl;
use app\models\Language;
use app\models\LanguagesSearch;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class LabelsController extends Controller
{
    /**
     * Render list of all languages
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new LabelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', compact('searchModel','dataProvider'));
    }

    /**
     * Creating a label (modal window)
     * @return array|string|Response
     */
    public function actionCreate()
    {
        $model = new Label();

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

                //save main object
                $model->save();

                //save translations
                foreach($model->translations as $lng => $word){
                    $trl = $model->getATrl($lng);
                    $trl->word = $word;
                    $trl->isNewRecord ? $trl->save() : $trl->update();
                }

                //back to list
                return $this->redirect(Url::to(['/admin/labels/index']));
            }
        }

        return $this->renderAjax('_edit',compact('model'));
    }

    /**
     * Updating a label (modal window)
     * @param $id
     * @return array|string|Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        /* @var $model Label */
        $model = Label::findOne((int)$id);

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Label not found'),404);
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

                //save main object
                $model->update();

                //save translations
                foreach($model->translations as $lng => $word){
                    $trl = $model->getATrl($lng);
                    $trl->word = $word;
                    $trl->isNewRecord ? $trl->save() : $trl->update();
                }

                //back to list
                return $this->redirect(Url::to(['/admin/labels/index']));
            }
        }

        return $this->renderAjax('_edit',compact('model'));
    }

    /**
     * Deleting labels
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        /* @var $model Language */
        $model = Label::findOne((int)$id);

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Label not found'),404);
        }

        $model->delete();

        return $this->redirect(Yii::$app->request->referrer);
    }
}