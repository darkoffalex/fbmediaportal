<?php

namespace app\controllers;

use app\helpers\Constants;
use app\helpers\Help;
use app\helpers\Sort;
use app\models\Comment;
use app\models\Language;
use app\models\Post;
use app\models\PostImage;
use app\models\PostSearchIndex;
use app\models\User;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use Yii;
use yii\web\Response;

class ApiController extends Controller
{
    /**
     * Get basic admin
     * @return User
     */
    private function getBasicAdmin()
    {
        /* @var $basicAdmin User */
        $basicAdmin = User::find()->where(['role_id' => Constants::ROLE_ADMIN, 'is_basic' => 1])->one();
        return !empty($basicAdmin) ? $basicAdmin : new User();
    }

    /**
     * Get first language
     * @return Language
     */
    private function getFirstLanguage()
    {
        /* @var $language Language */
        $language = Language::find()->orderBy('id ASC')->one();
        return $language;
    }

    /**
     * Find existing or create new user
     * @param string $fbId
     * @param string $name
     * @param string $surname
     * @param string $avatarUrl
     * @param null $email
     * @return User
     */
    private function getOrCreateUser($fbId,$name,$surname,$avatarUrl,$email = null)
    {
        /* @var $user User */

        $user = User::find()->where(['fb_user_id' => $fbId])->one();
        $user = empty($user) ? new User() : $user;

        $user->fb_user_id = $fbId;
        $user->name = $name;
        $user->surname = $surname;
        $user->avatar_file = $avatarUrl;
        $user->fb_avatar_url = $avatarUrl;
        $user->email = $email;

        if($user->isNewRecord){
            $user->status_id = Constants::STATUS_ENABLED;
            $user->type_id = Constants::USR_TYPE_IMPORTED;
            $user->role_id = Constants::ROLE_REGULAR_USER;
            $user->created_at = date('Y-m-d H:i:s',time());
            $user->created_by_id = $this->getBasicAdmin()->id;
        }

        $user->updated_at = date('Y-m-d H:i:s',time());
        $user->updated_by_id = $this->getBasicAdmin()->id;

        return $user;
    }

    /**
     * Checking the key
     * @param $key
     * @return bool
     */
    private function isCorrectApiKey($key)
    {
        /* @var $basicAdmin User */
        $basicAdmin = $this->getBasicAdmin();

        if(empty($basicAdmin->api_key) || empty($key)){
            return false;
        }

        return $basicAdmin->api_key == $key;
    }

    /**
     * Returns parameter from GET or POST request
     * @param $name
     * @param null $default
     * @return array|mixed
     */
    private function getRequestParam($name,$default = null)
    {
        return Yii::$app->request->get($name,Yii::$app->request->post($name,$default));
    }

    /**
     * Returns bot request arrays (POST and GET) merged
     * @return array
     */
    private function getRequest()
    {
        return ArrayHelper::merge(Yii::$app->request->get(),Yii::$app->request->post());
    }


    /**
     * Validate
     * @param $array
     * @param $rules
     * @param null $errorMsg
     * @return bool
     */
    private function validateRequest($array, $rules, &$errorMsg = null)
    {
        foreach($rules as $requiredAttribute){
            $value = ArrayHelper::getValue($array,$requiredAttribute,null);
            if(empty($value)){
                $errorMsg = "'".$requiredAttribute."' is not set";
                return false;
            }
        }

        return true;
    }

    /**
     * Run before every action
     * @param Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        //convert any array to JSON on response
        Yii::$app->response->format = Response::FORMAT_JSON;

        //disable CSRF validation
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Import single FB post
     * @param null $id
     * @return array
     */
    public function actionImportPost($id = null)
    {
        //check the key
        if(!$this->isCorrectApiKey($id)){
            return [
                'status' => 'ERROR',
                'message' => 'Authentication failed, check your key'
            ];
        }

        //get data from request
        $data = $this->getRequest();

        //error message (first error)
        $errorMsg = null;

        //required fields (for validation)
        $required = [
            'fb_id',
            'content',
            'group',
            'group.fb_id',
            'group.name',
            'author',
            'author.fb_id',
            'author.name',
            'author.surname',
        ];

        //if wrong format (lack of required fields)
        if(!$this->validateRequest($data,$required,$errorMsg)){
            return [
                'status' => 'ERROR',
                'message' => 'Wrong request. '.$errorMsg,
            ];
        }

        //transaction
        $transaction = Yii::$app->db->beginTransaction();

        try{
            //main parameters
            $post = new Post();
            $post->fb_sync_id = ArrayHelper::getValue($data,'fb_id');
            $post->name = ArrayHelper::getValue($data,'name');
            $post->content_type_id = Constants::CONTENT_TYPE_NEWS;
            $post->created_by_id = $this->getBasicAdmin()->id;
            $post->updated_by_id = $this->getBasicAdmin()->id;
            $post->created_at = date('Y-m-d H:i:s',time());
            $post->updated_at = date('Y-m-d H:i:s',time());
            $post->published_at = ArrayHelper::getValue($data,'publish_time');

            //videos
            $dataVideos = ArrayHelper::getValue($data,'videos');
            $dv = !empty($dataVideos[0]) ? $dataVideos[0] : null;
            $post->video_key_fb = ArrayHelper::getValue($dv,'fb_video_code');
            $post->video_key_yt = ArrayHelper::getValue($dv,'youtube_video_code');

            //author
            $dataAuthor = ArrayHelper::getValue($data,'author');
            $uFbId = ArrayHelper::getValue($dataAuthor,'fb_id');
            $uName = ArrayHelper::getValue($dataAuthor,'name');
            $uSurname = ArrayHelper::getValue($dataAuthor,'surname');
            $uAvatar = ArrayHelper::getValue($dataAuthor,'avatar_url');
            $uEmail = ArrayHelper::getValue($dataAuthor,'email');
            $author = $this->getOrCreateUser($uFbId,$uName,$uSurname,$uAvatar,$uEmail);
            $author->isNewRecord ? $author->save() : $author->update();
            $post->author_id = $author->id;

            //save post to database
            $post->save();

            //translatable data
            $trl = $post->getATrl();
            $trl->text = ArrayHelper::getValue($data,'content');
            $trl->name = ArrayHelper::getValue($data,'name');
            $trl->small_text = ArrayHelper::getValue($data,'content');
            $trl->isNewRecord ? $trl->save() : $trl->update();

            //images
            $imagesData = ArrayHelper::getValue($data,'images');
            if(!empty($imagesData)){
                foreach($imagesData as $url){
                    $image = new PostImage();
                    $image -> post_id = $post->id;
                    $image -> file_url = $url;
                    $image -> is_external = true;
                    $image -> status_id = Constants::STATUS_ENABLED;
                    $image -> priority = Sort::GetNextPriority(PostImage::className(),['post_id' => $post->id]);
                    $image -> created_by_id = $this->getBasicAdmin()->id;
                    $image -> updated_by_id = $this->getBasicAdmin()->id;
                    $image -> created_at = date('Y-m-d H:i:s',time());
                    $image -> updated_at = date('Y-m-d H:i:s',time());
                    $image -> save();
                }
            }

            //comments
            $commentsCreated = [];
            $commentsData = ArrayHelper::getValue($data, 'comments');
            if(!empty($commentsData)){
                foreach($commentsData as $cd){
                    $comment = new Comment();
                    $comment -> text = strip_tags(ArrayHelper::getValue($cd,'content'));
                    $comment -> post_id = $post->id;
                    $comment -> fb_sync_id = ArrayHelper::getValue($cd,'fb_id');
                    $comment -> answer_to_fb_id = ArrayHelper::getValue($cd,'answer_to_fb_id');
                    $comment -> created_at = date('Y-m-d H:i:s',time());
                    $comment -> updated_at = date('Y-m-d H:i:s',time());
                    $comment -> created_by_id = $this->getBasicAdmin()->id;
                    $comment -> updated_by_id = $this->getBasicAdmin()->id;

                    //author
                    $dataAuthor = ArrayHelper::getValue($cd,'author');
                    $uFbId = ArrayHelper::getValue($dataAuthor,'fb_id');
                    $uName = ArrayHelper::getValue($dataAuthor,'name');
                    $uSurname = ArrayHelper::getValue($dataAuthor,'surname');
                    $uAvatar = ArrayHelper::getValue($dataAuthor,'avatar_url');
                    $uEmail = ArrayHelper::getValue($dataAuthor,'email');
                    $cAuthor = $this->getOrCreateUser($uFbId,$uName,$uSurname,$uAvatar,$uEmail);
                    $cAuthor->isNewRecord ? $author->save() : $author->update();

                    $comment->author_id = $cAuthor->id;
                    $ok = $comment->save();

                    if($ok){
                        $commentsCreated[$comment->fb_sync_id] = $comment;
                    }
                }
            }

            /* @var $commentsCreated Comment[] */
            if(!empty($commentsCreated)){
                foreach($commentsCreated as $comment){
                    if(!empty($comment->answer_to_fb_id) && !empty($commentsCreated[$comment->answer_to_fb_id])){
                        $comment->answer_to_id = $commentsCreated[$comment->answer_to_fb_id]->id;
                        $comment->update();
                    }
                }
            }

            //update search indices
            $post->refresh();
            $post->updateSearchIndices();

            //apply changes
            $transaction->commit();

        }catch (\Exception $ex){
            //in case of error
            $transaction->rollBack();
            return [
                'status' => 'ERROR',
                'message' => $ex->getMessage()
            ];
        }


        //in case of success
        return [
            'status' => 'OK',
            'message' => 'All data imported successfully'
        ];
    }
}