<?php

namespace app\modules\admin\controllers;

use app\helpers\Sort;
use app\helpers\Help;
use app\models\Category;
use app\models\Language;
use app\models\Post;
use app\models\PostCategory;
use app\models\PostSearch;
use kartik\form\ActiveForm;
use Yii;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PostsController extends Controller
{
    /**
     * Render list of all posts that were imported or created by hand
     * @param null $lng
     * @return string
     */
    public function actionIndex($lng = null)
    {
        /* @var $languages Language[] */
        $languages = Language::find()->all();
        $lng = empty($lng) ? $languages[0]->prefix :$lng;


        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$lng);
        return $this->render('index', compact('searchModel','dataProvider','lng'));
    }

    /**
     * Creating a language (modal window)
     * @return array|string|Response
     */
    public function actionCreate()
    {
        $model = new Post();

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
            $model->type_id = Constants::POST_TYPE_CREATED;
            $model->author_id = Yii::$app->user->id;
            $model->created_at = date('Y-m-d H:i:s',time());
            $model->updated_at = date('Y-m-d H:i:s',time());
            $model->created_by_id = Yii::$app->user->id;
            $model->updated_by_id = Yii::$app->user->id;

            //if validated - save and go to list
            if($model->validate()){
                $model->save();
                return $this->redirect(Url::to(['/admin/posts/update', 'id' => $model->id]));
            }
        }

        return $this->renderAjax('_create',compact('model'));
    }

    /**
     * @param $id
     * @return array|string|Response
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        /* @var $model Post */
        $model = Post::findOne((int)$id);

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

                //update
                $model->update();

                //save translations
                foreach($model->translations as $lng => $attributes){
                    $trl = $model->getATrl($lng);
                    $trl->setAttributes($attributes);
                    $trl->isNewRecord ? $trl->save() : $trl->update();
                }

                //update categories (delete ignored)
                foreach($model->categories as $category){
                    if(!in_array($category->id,$model->categoriesChecked)){
                        PostCategory::deleteAll(['post_id' => $model->id, 'category_id' => $category->id]);
                    }
                }

                //update categories (add checked)
                $current = ArrayHelper::map($model->categories,'id','id');
                foreach($model->categoriesChecked as $catID){
                    if(!in_array($catID,$current)){
                        $pc = new PostCategory();
                        $pc->category_id = $catID;
                        $pc->post_id = $model->id;
                        $pc->save();
                    }
                }

                $model->refresh();
            }
        }

        return $this->render('edit',compact('model'));
    }
}