<?php

namespace app\modules\admin\controllers;

use app\helpers\Sort;
use app\helpers\Help;
use app\models\Category;
use app\models\Language;
use app\models\Post;
use app\models\PostCategory;
use app\models\PostImage;
use app\models\PostSearch;
use app\models\User;
use kartik\form\ActiveForm;
use Yii;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

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

                //save post
                if($model->save()){

                    //update counter (increase)
                    $model->author->counter_posts++;
                    $model->author->update();

                    return $this->redirect(Url::to(['/admin/posts/update', 'id' => $model->id]));
                }
            }

            return $this->redirect(Url::to(['/admin/posts/index']));
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

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Post not found'),404);
        }

        //ajax validation
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        //if something coming from POST
        if(Yii::$app->request->isPost){

            /* @var $oldAuthor User */
            $oldAuthor = $model->author;

            /* @var $newAuthor User */
            $newAuthor = null;

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

                //update positions for categories
                foreach($model->categoriesStickyPositions as $cpID => $stickyPosition){
                    $postId = explode('_',$cpID)[0];
                    $categoryId = explode('_',$cpID)[1];
                    $pc = PostCategory::findOne(['category_id' => $categoryId,'post_id' => $postId]);
                    if(!empty($pc)){
                        $pc->sticky_position = $stickyPosition;
                        $pc->update();
                    }
                }

                //load all related stuff again
                $model->refresh();

                //retrieve new author
                $newAuthor = $model->author;

                //if post moved from one author to another - update counters for both
                if(ArrayHelper::getValue($newAuthor,'id') != ArrayHelper::getValue($oldAuthor,'id')){
                    if(!empty($oldAuthor)){
                        $oldAuthor->counter_posts = count($oldAuthor->posts);
                        $oldAuthor->update();
                    }
                    if(!empty($newAuthor)){
                        $newAuthor->counter_posts = count($newAuthor->posts);
                        $newAuthor->update();
                    }
                //update just current's author's counter (for confidence)
                }else{
                    if(!empty($model->author)){
                        $model->author->counter_posts = count($model->author->posts);
                        $model->author->update();
                    }
                }
            }
        }

        return $this->render('edit',compact('model'));
    }


    /**
     * Uploading images via ajax
     * @param $id
     * @return array|string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionCreateImage($id)
    {
        /* @var $post Post */
        $post = Post::findOne((int)$id);

        if(empty($post)){
            throw new NotFoundHttpException(Yii::t('admin','Post not found'),404);
        }

        //new image model
        $model = new PostImage();

        //if post given
        if(Yii::$app->request->isPost){

            //load all necessary data
            $model->load(Yii::$app->request->post());
            $model->image = UploadedFile::getInstance($model,'image');

            //if all data valid
            if($model->validate()){

                //if should load from file
                if(!$model->is_external){

                    //generate name
                    $filename = Yii::$app->security->generateRandomString(16).'.'.$model->image->extension;

                    //try save to dir
                    try{
                        if($model->image->saveAs(Yii::getAlias('@webroot/uploads/img/'.$filename))){
                            $model->file_path = $filename;
                        }
                    }catch (\Exception $ex){
                        Help::log('upload_errors.txt',$ex->getMessage());
                    }
                }

                //set basic settings and save
                $model->created_at = date('Y-m-d H:i:s', time());
                $model->updated_at = date('Y-m-d H:i:s', time());
                $model->created_by_id = Yii::$app->user->id;
                $model->updated_by_id = Yii::$app->user->id;
                $model->priority = Sort::GetNextPriority(PostImage::className(),['post_id' => $post->id]);
                $model->post_id = $post->id;
                $model->image = null;
                $saved = $model->save();

                //save translations if base object was saved
                if($saved){
                    foreach($model->translations as $lng => $attributes){
                        $trl = $model->getATrl($lng);
                        $trl->setAttributes($attributes);
                        $trl->isNewRecord ? $trl->save() : $trl->update();
                    }
                }

                //refresh post
                $post->refresh();

                //It's ok, can reload table
                return 'OK';
            }
        }

        //render form
        return $this->renderAjax('_edit_image',compact('model','post'));
    }
}