<?php

namespace app\modules\admin\controllers;

use app\helpers\Sort;
use app\helpers\Help;
use app\models\Category;
use app\models\Comment;
use app\models\Language;
use app\models\Post;
use app\models\PostCategory;
use app\models\PostGroup;
use app\models\PostImage;
use app\models\PostSearch;
use app\models\PostVoteAnswer;
use app\models\User;
use kartik\form\ActiveForm;
use linslin\yii2\curl\Curl;
use Yii;
use app\helpers\Constants;
use yii\base\Exception;
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

                    /* @var $firstLng Language */
                    $firstLng = Language::find()->orderBy('id ASC')->one();
                    if(!empty($firstLng)){
                        $trl = $model->getATrl($firstLng->prefix);
                        $trl -> name = $model->name;
                        $trl -> isNewRecord ? $trl->save() : $trl->update();
                    }

                    $model->updateSearchIndices([Constants::IND_R_CONTENT]);

                    return $this->redirect(Url::to(['/admin/posts/update', 'id' => $model->id]));
                }
            }

            return $this->redirect(Url::to(['/admin/posts/index']));
        }

        return $this->renderAjax('_create',compact('model'));
    }

    /**
     * Update post
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
     * Crop image
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionCropImage($id)
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

            //if all data valid
            if($model->validate()){

                //update cropping positions
                $model->update();

                //remove cropped image
                $model->clearCropped();

                //It's ok, can reload table
                return 'OK';
            }
        }

        //render form
        return $this->renderAjax('_crop_image',compact('model','post'));
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
        $post = Post::find()->where(['id' => $id])->one();

        if(empty($post)){
            throw new NotFoundHttpException(Yii::t('admin','Post not found'),404);
        }

        return $this->renderAjax('_comments',compact('post'));
    }

    /**
     * Refresh comments if needed
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionRefresh($id)
    {
        /* @var $post Post */
        $post = Post::find()->where(['id' => $id])->one();

        if(empty($post)){
            throw new NotFoundHttpException(Yii::t('admin','Post not found'),404);
        }

        $log = $this->updateComments($post);
        $post->refresh();

        $author = $post->author;
        if(!empty(($author))){
            $author->counter_comments = Comment::find()->where(['author_id' => $author->id])->count();
            $author->update();
        }

        //back to previous page
        return $this->redirect(Yii::$app->request->referrer);
    }


    ////////////////////////////// A D M I N I Z A T O R  A P I  F U N C T I O N S ////////////////////////////////////


    /**
     * Retrieves an api data
     * @param $id
     * @param $type
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    private function getApiPostData($id,$type,$params = [])
    {
        $url = 'https://adminizator.com/api/post/'.$id.'/'.$type;
        if(!empty($params)){
            $url.='?'.http_build_query($params);
        }

        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT,15);
        $curl->setOption(CURLOPT_CONNECTTIMEOUT,15);
        $response = $curl->get($url);

        if($curl->responseCode != 200){
            throw neW Exception("Connection error",$curl->errorCode);
        }

        return json_decode($response,true);
    }

    /**
     * Retrieve author-user by data
     * @param $authorData
     * @return User|array|null|\yii\db\ActiveRecord
     */
    private function getAuthor($authorData)
    {
        $log = "";
        $fbId = ArrayHelper::getValue($authorData,'id');
        $name = ArrayHelper::getValue($authorData,'name');
        $surname = ArrayHelper::getValue($authorData,'surname');
        $avatar_url = ArrayHelper::getValue($authorData,'avatar_url');

        if(!empty($fbId)){
            $user = User::find()->where(['fb_user_id' => $fbId])->one();

            if(empty($user)){
                $log .= "Creating new author-user with FB ID (app context) {$fbId} \n";

                try{
                    $user = new User();
                    $user->fb_user_id = $fbId;
                    $user->name = $name;
                    $user->username = $fbId;
                    $user->password = 'dummy_string';
                    $user->surname = $surname;
                    $user->avatar_file = $avatar_url;
                    $user->fb_avatar_url = $avatar_url;
                    $user->email = null;
                    $user->updated_at = date('Y-m-d H:i:s',time());
                    $user->updated_by_id = Yii::$app->user->id;
                    $user->status_id = Constants::STATUS_ENABLED;
                    $user->role_id = Constants::ROLE_REGULAR_USER;
                    $user->type_id = Constants::USR_TYPE_IMPORTED;
                    $user->save();
                }catch (\Exception $ex){
                    $log .= "ERROR: ".$ex->getMessage()."\n";
                    return null;
                }

            }else{
                $log .= "Author-user with FB ID (app context) {$fbId} found \n";
            }

            return $user;
        }

        return null;
    }

    /**
     * Updating post's comments
     * @param $post
     * @return string
     */
    private function updateComments($post)
    {
        /* @var $post Post */

        $log = "";
        $pageProcessing = 1;
        while(true){
            $log .= "Querying adminizator API for comments (page {$pageProcessing}) \n";
            $data = $this->getApiPostData($post->fb_sync_id,'comments',['page' => $pageProcessing]);

            $total = ArrayHelper::getValue($data,'total');
            $currentPage = ArrayHelper::getValue($data,'currentPage');
            $lastPage = ArrayHelper::getValue($data,'lastPage');
            $perPage = ArrayHelper::getValue($data,'perPage');
            $items = ArrayHelper::getValue($data,'items');
            $onPage = count($items);

            if(empty($data) || empty($currentPage) || empty($items)){
                $log .= "Can't retrieve data \n";
                break;
            }else{
                $log .= "Found {$onPage} comments. Processing... \n";
            }

            foreach($items as $itemData){
                $fbId = ArrayHelper::getValue($itemData,'id');
                $sysId = (int)ArrayHelper::getValue($itemData,'system_id');
                $answerToSysId = (int)ArrayHelper::getValue($itemData,'answer_to_id');
                $content = ArrayHelper::getValue($itemData,'content');
                $time = ArrayHelper::getValue($itemData,'published_time');
                $authorData = ArrayHelper::getValue($itemData,'author');

                /* @var $comment Comment */
                $comment = Comment::find()->where(['fb_sync_id' => $fbId, 'post_id' => $post->id])->one();
                if(empty($comment)){
                    $log .= "Creating comment with FB ID {$fbId} for post {$post->id} \n";
                    $comment = new Comment();
                    $comment -> post_id = $post->id;
                    $comment -> fb_sync_id = $fbId;
                    $comment -> adm_id = $sysId;
                    $comment -> answer_to_adm_id = $answerToSysId;
                    $comment -> text = $content;
                    $comment -> created_at = $time;
                    $comment -> updated_at = date('Y-m-d H:i:s',time());
                    $comment -> created_by_id = Yii::$app->user->id;
                    $comment -> updated_by_id = Yii::$app->user->id;

                    $author = $this->getAuthor($authorData);
                    $comment -> author_id = ArrayHelper::getValue($author,'id',null);

                    if(!empty($post->author_id)){
                        $log .= "Author {$author->id} with FB ID (app context) {$author->fb_user_id} assigned to comment \n";
                    }else{
                        $log .= "ERROR: Author not assigned \n";
                    }

                    if($comment->save()){
                        $log .= "Comment with FB ID {$comment->fb_sync_id} added to post {$post->id} \n";
                    }else{
                        $log .= "ERROR: Can't add comment \n";
                    }
                }else{
                    $log .= "Comment already added \n";
                }
            }

            if($pageProcessing >= $lastPage){
                $post->refresh();
                $added = count($post->comments);

                $log .= "Comments adding done. Post has {$added} comments \n";
                break;
            }

            $pageProcessing++;
        }

        if(!empty($post->comments)){
            $log .= "Building nested relations, updating... \n";
            foreach($post->comments as $comment){
                if(!empty($comment->admParent)){
                    $comment->answer_to_id = $comment->admParent->id;
                    $comment->update();
                }
            }
            $log .= "Nested relations updated \n";
        }

        $log .= "Comment adding finished";

        return $log;
    }
}