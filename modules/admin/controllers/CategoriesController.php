<?php

namespace app\modules\admin\controllers;

use app\helpers\Sort;
use app\helpers\Help;
use app\models\Category;
use app\models\CategorySearch;
use kartik\form\ActiveForm;
use Yii;
use app\helpers\Constants;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CategoriesController extends Controller
{
    /**
     * Render category list
     * @param int $root
     * @return string
     */
    public function actionIndex($root = 0)
    {
        $root = Yii::$app->request->post('expandRowKey',$root);

        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$root);

        if(Yii::$app->request->isAjax){
            return $this->renderAjax('_index', compact('searchModel','dataProvider','root'));
        }

        return $this->render('index', compact('searchModel','dataProvider','root'));
    }

    /**
     * Moves category (up or down, changes priority)
     * @param int $id
     * @param string $dir
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionMove($id, $dir)
    {
        /* @var $model Category */
        $model = Category::findOne((int)$id);

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Category not found'),404);
        }

        Sort::Move($model,$dir,Category::className(),['parent_category_id' => $model->parent_category_id]);

        if(Yii::$app->request->isAjax){
            return $this->actionIndex($model->parent_category_id);
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Delete
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        /* @var $model Category */
        $model = Category::findOne((int)$id);

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Category not found'),404);
        }

        $parentId = $model->parent_category_id;
        $model->recursiveDelete();

        if(Yii::$app->request->isAjax){
            return $this->actionIndex($parentId);
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Creating a category (modal window with base settings)
     * @return array|string|Response
     */
    public function actionCreate()
    {
        $model = new Category();

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

            //calculate new priority
            $model->priority = Sort::GetNextPriority(Category::className(),['parent_category_id' => $model->parent_category_id]);

            //if validated - save and go to detail edit
            if($model->validate()){
                $model->save();
                return $this->redirect(Url::to(['/admin/categories/edit', 'id' => $model->id]));
            }
        }

        return $this->renderAjax('_create',compact('model'));
    }

    /**
     * Editing of category settings
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionEdit($id)
    {
        /* @var $model Category */
        $model = Category::findOne((int)$id);

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Category not found'),404);
        }

        //if something coming from POST
        if(Yii::$app->request->isPost){

            //load data
            $model->load(Yii::$app->request->post());;

            //set some statistics info
            $model->updated_at = date('Y-m-d H:i:s',time());
            $model->updated_by_id = Yii::$app->user->id;

            //if validated - save
            if($model->validate()){

                //update main object
                $model->update();

                //save translations
                foreach($model->translations as $lng => $attributes){
                    $trl = $model->getATrl($lng);
                    $trl->setAttributes($attributes);
                    $trl->isNewRecord ? $trl->save() : $trl->update();
                }

            }
        }

        return $this->render('edit',compact('model'));
    }
}