<?php

namespace app\modules\admin\controllers;

use app\helpers\Sort;
use app\helpers\Help;
use app\models\Category;
use app\models\Language;
use app\models\Post;
use app\models\PostCategory;
use app\models\PostGroup;
use app\models\PostImage;
use app\models\PostSearch;
use app\models\PostSources;
use app\models\PostVoteAnswer;
use app\models\User;
use kartik\form\ActiveForm;
use Yii;
use app\helpers\Constants;
use yii\base\InvalidParamException;
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

                //update search indexes
                $model->updateSearchIndices([Constants::IND_R_CONTENT,Constants::IND_R_CATEGORIES]);

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
     * Delete post
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        /* @var $post Post */
        $post = Post::findOne((int)$id);

        if(empty($post)){
            throw new NotFoundHttpException(Yii::t('admin','Post not found'),404);
        }

        //delete all related image's files
        if(!empty($post->postImages)){
            foreach($post->postImages as $image){
                $image->deleteFile();
            }
        }

        //delete post itself
        $post->delete();

        //back to previous page
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Listing all images related with post
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionListImages($id)
    {
        /* @var $post Post */
        $post = Post::findOne((int)$id);

        if(empty($post)){
            throw new NotFoundHttpException(Yii::t('admin','Post not found'),404);
        }

        return $this->renderPartial('_images',compact('post'));
    }

    /**
     * Delete image of post
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionDeleteImage($id)
    {
        /* @var $image PostImage */
        $image = PostImage::findOne((int)$id);
        $post = $image->post;
        $postId = $image->post_id;

        if(empty($image)){
            throw new NotFoundHttpException(Yii::t('admin','Image not found'),404);
        }

        //delete
        $image->deleteFile();
        $image->delete();

        //update search index
        $post->updateSearchIndices([Constants::IND_R_IMAGES]);

        return $this->actionListImages($postId);
    }

    /**
     * Move priority
     * @param $id
     * @param $dir
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionMoveImage($id,$dir)
    {
        /* @var $image PostImage */
        $image = PostImage::findOne((int)$id);
        $postId = $image->post_id;

        if(empty($image)){
            throw new NotFoundHttpException(Yii::t('admin','Image not found'),404);
        }

        Sort::Move($image,$dir,PostImage::className(),['post_id' => $postId]);

        return $this->actionListImages($postId);
    }

    /**
     * Editing images
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionEditImage($id)
    {
        /* @var $model PostImage */
        $model = PostImage::findOne((int)$id);

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Image not found'),404);
        }

        $post = $model->post;

        //if post given
        if(Yii::$app->request->isPost){

            //load all necessary data
            $model->load(Yii::$app->request->post());
            $model->image = UploadedFile::getInstance($model,'image');

            //if all data valid
            if($model->validate()){

                //if should load from file
                if(!$model->is_external){

                    if(!empty($model->image) && $model->image->size > 0){
                        //generate name
                        $filename = Yii::$app->security->generateRandomString(16).'.'.$model->image->extension;

                        //try save to dir
                        try{
                            //delete old file
                            $model->deleteFile();

                            //save new file and set filename
                            if($model->image->saveAs(Yii::getAlias('@webroot/uploads/img/'.$filename))){
                                $model->file_path = $filename;
                            }
                        }catch (\Exception $ex){
                            Help::log('upload_errors.txt',$ex->getMessage());
                            $model->addError('image','Error while saving file');
                        }
                    }else{
                        //file wasn't uploaded recently
                        if(!$model->hasFile()){
                            $model->addError('image',Yii::t('admin','Please select file'));
                        }
                    }
                //if external
                }else{
                    //delete old file
                    $model->deleteFile();
                }

                //if form has no errors
                if(!$model->hasErrors()){

                    //set basic settings and save
                    $model->updated_at = date('Y-m-d H:i:s', time());
                    $model->updated_by_id = Yii::$app->user->id;
                    $model->image = null;
                    $model->update();

                    //save translations if base object was saved
                    if(!$model->hasErrors()){
                        foreach($model->translations as $lng => $attributes){
                            $trl = $model->getATrl($lng);
                            $trl->setAttributes($attributes);
                            $trl->isNewRecord ? $trl->save() : $trl->update();
                        }
                    }

                    //refresh post
                    $post->refresh();

                    //update search index
                    $post->updateSearchIndices([Constants::IND_R_IMAGES]);

                    //It's ok, can reload table
                    return 'OK';
                }
            }
        }

        //render form
        return $this->renderAjax('_edit_image',compact('model','post'));
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

                    if(!empty($model->image) && $model->image->size > 0){
                        //generate name
                        $filename = Yii::$app->security->generateRandomString(16).'.'.$model->image->extension;

                        //try save to dir
                        try{
                            if($model->image->saveAs(Yii::getAlias('@webroot/uploads/img/'.$filename))){
                                $model->file_path = $filename;
                            }
                        }catch (\Exception $ex){
                            Help::log('upload_errors.txt',$ex->getMessage());
                            $model->addError('image','Error while saving file');
                        }
                    }else{
                        $model->addError('image',Yii::t('admin','Please select file'));
                    }
                }

                //if form has no errors
                if(!$model->hasErrors()){

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

                    //update search index
                    $post->updateSearchIndices([Constants::IND_R_IMAGES]);

                    //It's ok, can reload table
                    return 'OK';
                }
            }
        }

        //render form
        return $this->renderAjax('_edit_image',compact('model','post'));
    }

    /**
     * Update or create answer
     * @param null|int $post_id
     * @param null|int $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionUpdateAnswer($post_id = null, $id = null)
    {
        //if creating (given just post_id)
        if(!empty($post_id)){
            /* @var $post Post */
            $post = Post::findOne((int)$post_id);

            if(empty($post)){
                throw new NotFoundHttpException(Yii::t('admin','Post not found'),404);
            }
            $model = new PostVoteAnswer();

        //if updating (empty post_id, but have id)
        }elseif(!empty($id)){
            $model = PostVoteAnswer::findOne((int)$id);

            if(empty($model)){
                throw new NotFoundHttpException(Yii::t('admin','Answer not found'),404);
            }
            $post = $model->post;
        //or wrong parameters
        }else{
            throw new InvalidParamException(Yii::t('admin','Wrong parameters'),404);
        }

        //if post given
        if(Yii::$app->request->isPost){

            //load all necessary data
            $model->load(Yii::$app->request->post());

            //if all data is valid
            if($model->validate()){

                if($model->isNewRecord){
                    $model->post_id = $post->id;
                    $model->created_at = date('Y-m-d H:i:s', time());
                    $model->created_by_id = Yii::$app->user->id;
                    $model->priority = Sort::GetNextPriority(PostVoteAnswer::className(),['post_id' => $post->id]);
                }

                $model->updated_at = date('Y-m-d H:i:s', time());
                $model->updated_by_id = Yii::$app->user->id;
                $model->isNewRecord ? $model->save() : $model->update();

                //if successfully saved or updated - update translations
                if(!$model->hasErrors()){
                    foreach($model->translations as $lng => $attributes){
                        $trl = $model->getATrl($lng);
                        $trl->setAttributes($attributes);
                        $trl->isNewRecord ? $trl->save() : $trl->update();
                    }
                }

                //update search index
                $post->updateSearchIndices([Constants::IND_R_ANSWERS]);

                //OK message (to reload container)
                return 'OK';

            }

        }

        //render form
        return $this->renderAjax('_edit_answer',compact('model','post'));
    }

    /**
     * Listing all answers related with post
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionListAnswers($id)
    {
        /* @var $post Post */
        $post = Post::findOne((int)$id);

        if(empty($post)){
            throw new NotFoundHttpException(Yii::t('admin','Post not found'),404);
        }

        return $this->renderPartial('_answers',compact('post'));
    }

    /**
     * Deleting answer variant
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDeleteAnswer($id)
    {
        /* @var $answer PostImage */
        $answer = PostVoteAnswer::findOne((int)$id);
        $post = $answer->post;
        $postId = $answer->post_id;

        if(empty($answer)){
            throw new NotFoundHttpException(Yii::t('admin','Answer not found'),404);
        }

        //delete
        $answer->delete();

        //update search index
        $post->updateSearchIndices([Constants::IND_R_ANSWERS]);

        return $this->actionListAnswers($postId);
    }


    /**
     * Update group selection field (after new item adding)
     * @throws NotFoundHttpException
     */
    public function actionGroupIdUpdate()
    {
        /* @var $groups PostGroup[] */
        $groups = PostGroup::find()->all();

        $noneLabel = Yii::t('admin','[NONE]');
        $result = "<option value=''>{$noneLabel}</option>";

        foreach($groups as $group){
            $result.="<option value='{$group->id}'>{$group->name}</option>";
        }

        return $result;
    }

    /**
     * Creating new group via modal window
     * @return string
     */
    public function actionCreateGroup()
    {
        $model = new PostGroup();

        if(Yii::$app->request->isPost && $model->load(Yii::$app->request->post())){
            if($model->validate()){
                $model->created_at = date('Y-m-d H:i:s', time());
                $model->updated_at = date('Y-m-d H:i:s', time());
                $model->created_by_id = Yii::$app->user->id;
                $model->updated_by_id = Yii::$app->user->id;
                $model->save();

                return 'OK';
            }
        }

        return $this->renderAjax('_edit_group',compact('model'));
    }

    /**
     * View post's comments
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionComments($id)
    {
        /* @var $post Post */
        $post = Post::findOne((int)$id);

        if(empty($post)){
            throw new NotFoundHttpException(Yii::t('admin','Post not found'),404);
        }

        return $this->renderAjax('_comments',compact('post'));
    }
}