<?php
namespace app\commands;

use app\helpers\Help;
use app\helpers\Sort;
use app\models\Comment;
use app\models\Post;
use app\models\PostGroup;
use app\models\PostImage;
use yii\console\Controller;
use Yii;
use app\models\User;
use app\helpers\Constants;
use app\models\Language;
use yii\console\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use linslin\yii2\curl\Curl;

class SyncController extends Controller
{


    public $limit = null;
    public $api = "https://adminizator.com/api/";
    public $timeout = 15;
    public $type = 'stock'; //available values : stock, non stock
    public function options($actionID)
    {
        return ['limit','api', 'timeout', 'type'];
    }

    /**
     * @var User
     */
    private $_basicAdmin;
    /**
     * Get basic admin (singleton style)
     * @return User
     */
    private function getBasicAdmin()
    {
        /* @var $basicAdmin User */
        if(empty($this->_basicAdmin)){
            $this->_basicAdmin = User::find()->where(['role_id' => Constants::ROLE_ADMIN, 'is_basic' => 1])->one();
            $this->_basicAdmin = !empty($this->_basicAdmin) ? $this->_basicAdmin : new User();
        }

        return $this->_basicAdmin;
    }

    /**
     * @var Language
     */
    private $_firstLanguage = null;
    /**
     * Get first language (singleton style)
     * @return Language
     */
    private function getFirstLanguage()
    {
        /* @var $language Language */
        if(empty($this->_firstLanguage)){
            $this->_firstLanguage = Language::find()->orderBy('id ASC')->one();
        }

        return $this->_firstLanguage;
    }

    /**
     * Retrieves an api data
     * @param $id
     * @param $type
     * @param array $params
     * @return mixed
     */
    private function getApiPostData($id,$type,$params = [])
    {
        $url = $this->api.'post/'.$id.'/'.$type;
        if(!empty($params)){
            $url.='?'.http_build_query($params);
        }

        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT,$this->timeout);
        $curl->setOption(CURLOPT_CONNECTTIMEOUT,$this->timeout);
        $response = $curl->get($url);

        if($curl->responseCode != 200){
            echo "ERROR: connection error \n";
            return null;
        }

        return json_decode($response,true);
    }

    /**
     * Retrieve group by data
     * @param $groupData
     * @return PostGroup|null
     */
    private function getGroup($groupData)
    {
        /* @var $group PostGroup */

        $fbId = ArrayHelper::getValue($groupData,'id');
        $name = ArrayHelper::getValue($groupData,'name');

        if(!empty($fbId) && !empty($name)){

            $group = PostGroup::find()->where(['fb_sync_id' => $fbId])->one();

            if(empty($group)){
                echo "Creating source group with FB ID {$fbId} for post \n";

                $group = new PostGroup();
                $group -> fb_sync_id = $fbId;
                $group -> name = $name;
                $group -> url = "https://www.facebook.com/groups/{$fbId}/";
                $group -> is_group = (int)true;
                $group -> created_by_id = $this->getBasicAdmin()->id;
                $group -> updated_by_id = $this->getBasicAdmin()->id;
                $group -> created_at = date('Y-m-d H:i:s',time());
                $group -> updated_at = date('Y-m-d H:i:s',time());
                $group -> save();
            }else{
                echo "Group with FB ID {$fbId} found \n";
            }

            return $group;
        }

        return null;
    }

    /**
     * Retrieve author-user by data
     * @param $authorData
     * @return User|array|null|\yii\db\ActiveRecord
     */
    private function getAuthor($authorData)
    {
        $fbId = ArrayHelper::getValue($authorData,'id');
        $name = ArrayHelper::getValue($authorData,'name');
        $surname = ArrayHelper::getValue($authorData,'surname');
        $avatar_url = ArrayHelper::getValue($authorData,'avatar_url');

        if(!empty($fbId)){
            $user = User::find()->where(['fb_user_id' => $fbId])->one();

            if(empty($user)){
                echo "Creating new author-user with FB ID (app context) {$fbId} \n";

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
                    $user->updated_by_id = $this->getBasicAdmin()->id;
                    $user->status_id = Constants::STATUS_ENABLED;
                    $user->role_id = Constants::ROLE_REGULAR_USER;
                    $user->type_id = Constants::USR_TYPE_IMPORTED;
                    $user->save();
                }catch (\Exception $ex){
                    echo "ERROR: ".$ex->getMessage()."\n";
                    return null;
                }

            }else{
                echo "Author-user with FB ID (app context) {$fbId} found \n";
            }

            return $user;
        }

        return null;
    }

    /**
     * Updating post's comments
     * @param $post
     * @throws \Exception
     */
    private function updateComments($post)
    {
        /* @var $post Post */

        $pageProcessing = 1;
        while(true){
            echo "Querying adminizator API for comments (page {$pageProcessing}) \n";
            $data = $this->getApiPostData($post->fb_sync_id,'comments',['page' => $pageProcessing]);

            $total = ArrayHelper::getValue($data,'total');
            $currentPage = ArrayHelper::getValue($data,'currentPage');
            $lastPage = ArrayHelper::getValue($data,'lastPage');
            $perPage = ArrayHelper::getValue($data,'perPage');
            $items = ArrayHelper::getValue($data,'items');
            $onPage = count($items);

            if(empty($data) || empty($currentPage) || empty($items)){
                echo "Can't retrieve data \n";
                break;
            }else{
                echo "Found {$onPage} comments. Processing... \n";
            }

            foreach($items as $itemData){
                $fbId = ArrayHelper::getValue($itemData,'id');
                $sysId = (int)ArrayHelper::getValue($itemData,'system_id');
                $answerToSysId = (int)ArrayHelper::getValue($itemData,'answer_to_id');
                $content = ArrayHelper::getValue($itemData,'content');
                $time = ArrayHelper::getValue($itemData,'published_time');
                $authorData = ArrayHelper::getValue($itemData,'author');

                /* @var $comment Comment */
                $comment = Comment::find()->where(['fb_sync_id' => $fbId, 'post_id' => $post->id, 'adm_id' => $sysId])->one();
                if(empty($comment)){
                    echo "Creating comment with FB ID {$fbId} for post {$post->id} \n";
                    $comment = new Comment();
                    $comment -> post_id = $post->id;
                    $comment -> fb_sync_id = $fbId;
                    $comment -> adm_id = $sysId;
                    $comment -> answer_to_adm_id = $answerToSysId;
                    $comment -> text = $content;
                    $comment -> created_at = $time;
                    $comment -> updated_at = date('Y-m-d H:i:s',time());
                    $comment -> created_by_id = $this->getBasicAdmin()->id;
                    $comment -> updated_by_id = $this->getBasicAdmin()->id;

                    $author = $this->getAuthor($authorData);
                    $comment -> author_id = ArrayHelper::getValue($author,'id',null);

                    if(!empty($post->author_id)){
                        echo "Author {$author->id} with FB ID (app context) {$author->fb_user_id} assigned to comment \n";
                    }else{
                        echo "ERROR: Author not assigned \n";
                    }

                    if($comment->save()){
                        echo "Comment with FB ID {$comment->fb_sync_id} added to post {$post->id} \n";
                    }else{
                        echo "ERROR: Can't add comment \n";
                    }
                }else{
                    echo "Comment already added \n";
                }
            }

            if($pageProcessing >= $lastPage){
                $post->refresh();
                $added = count($post->comments);

                echo "Comments adding done. Post has {$added} comments \n";
                break;
            }

            $pageProcessing++;
        }

        if(!empty($post->comments)){
            echo "Building nested relations, updating... \n";
            foreach($post->comments as $comment){
                if(!empty($comment->admParent)){
                    $comment->answer_to_id = $comment->admParent->id;
                    $comment->update();
                }
            }
            echo "Nested relations updated \n";
        }

        echo "Comment adding finished";
    }

    /**
     * Updates all post which has facebook relation. Retrieves data, comments, images from adminizator
     * @param string $lng
     * @throws Exception
     * @throws \Exception
     */
    public function actionIndex($lng = 'ru')
    {
        if(!is_numeric($this->timeout)){
            throw new Exception("Timeout should be numeric value");
        }

        $q = Post::find()->where('fb_sync_id IS NOT NULL');
        if(!empty($this->limit)){
            if(!is_numeric($this->limit)){
                throw new Exception("Limit should be numeric value");
            }
            $q->limit($this->limit);
        }

        if($this->type != 'stock'){
            $q->where('status_id != :status',['status' => Constants::STATUS_IN_STOCK]);
        }else{
            $q->where(['status_id' => Constants::STATUS_IN_STOCK]);
        }

        //get all posts related with facebook
        $searchIn = $this->type == 'stock' ? 'STOCK' : 'NON STOCK';
        echo "Getting all posts related with facebook ({$searchIn}) \n";
        /* @var $posts Post[] */

        $posts = $q->all();

        //if found some posts in database
        if(!empty($posts)){

            $count = count($posts);
            echo "Found {$count} posts. Processing... \n";

            foreach($posts as $post){
                echo "Processing post {$post->id} \n";

                echo "Querying adminizator API for post details \n";
                $data = $this->getApiPostData($post->fb_sync_id,'details');

                if(empty($data)){
                    echo "Not found data for post {$post->id} \n";
                    echo "\n\n\n";
                    continue;
                }

                $type = ArrayHelper::getValue($data,'type');
                $content = ArrayHelper::getValue($data,'content');
                $time = ArrayHelper::getValue($data,'published_time');
                $groupData = ArrayHelper::getValue($data,'group');
                $authorData = ArrayHelper::getValue($data,'author');
                $attachments = ArrayHelper::getValue($data,'attachments');
                $commentsCount = ArrayHelper::getValue($data,'comments_count');

                $typeMatches = [
                    Constants::FB_POST_EVENT => Constants::CONTENT_TYPE_NEWS,
                    Constants::FB_POST_LINK => Constants::CONTENT_TYPE_NEWS,
                    Constants::FB_POST_PHOTO => Constants::CONTENT_TYPE_PHOTO,
                    Constants::FB_POST_VIDEO => Constants::CONTENT_TYPE_VIDEO,
                    Constants::FB_POST_STATUS => Constants::CONTENT_TYPE_NEWS,
                ];

                $post->content_type_id = ArrayHelper::getValue($typeMatches,$type,Constants::CONTENT_TYPE_NEWS);
                $post->updated_at = date('Y-m-d H:i:s',time());
                $post->published_at = $time;
                $post->status_id = $post->status_id == Constants::STATUS_IN_STOCK ? Constants::STATUS_ENABLED : $post->status_id;

                $trl = $post->getATrl($lng);
                $trl -> text = strip_tags($content);
                $trl -> small_text = StringHelper::truncateWords($trl->text,20);
                $trl -> isNewRecord ? $trl->save() : $trl->update();

                $group = $this->getGroup($groupData);
                $post -> group_id = ArrayHelper::getValue($group,'id',null);

                if(!empty($post->group_id)){
                    echo "Group {$post->group_id} with FB ID {$group->fb_sync_id} assigned to post \n";
                }else{
                    echo "ERROR: Group not assigned \n";
                }

                $user = $this->getAuthor($authorData);
                $post -> author_id = ArrayHelper::getValue($user,'id',null);
                if(!empty($user)) $post->author_custom_name = $user->name.' '.$user->surname;

                if(!empty($post->author_id)){
                    echo "Author {$user->id} with FB ID (app context) {$user->fb_user_id} assigned to post \n";
                }else{
                    echo "ERROR: Author not assigned \n";
                }

                echo "Updating post.. \n";
                $post->update();

                echo "Updating attachments.. \n";
                if(!empty($attachments)){
                    foreach($attachments as $attachment){
                        $aType = ArrayHelper::getValue($attachment,'type');
                        $aFbId = ArrayHelper::getValue($attachment,'id');
                        $aImageUrl = ArrayHelper::getValue($attachment,'image_url');
                        $aVideoUrl = ArrayHelper::getValue($attachment,'origin_url');

                        if($aType == 'photo'){
                            /* @var $image PostImage */
                            $image = PostImage::find()->where(['fb_sync_id' => $user->id, 'post_id' => $post->id])->one();

                            if(empty($image)){
                                echo "Creating photo attachment with FB ID {$aFbId} \n";
                                $image = new PostImage();
                                $image -> fb_sync_id = $aFbId;
                                $image -> is_external = 1;
                                $image -> file_url = $aImageUrl;
                                $image -> post_id = $post->id;
                                $image -> status_id = Constants::STATUS_ENABLED;
                                $image -> priority = Sort::GetNextPriority(PostImage::className(),['post_id' => $post->id]);
                                $image -> created_at = date('Y-m-d H:i:s',time());
                                $image -> updated_at = date('Y-m-d H:i:s',time());
                                $image -> created_by_id = $this->getBasicAdmin()->id;
                                $image -> updated_by_id = $this->getBasicAdmin()->id;
                                $image -> save();
                            }else{
                                if($image->file_url != $aImageUrl){
                                    echo "Updating photo attachment with FB ID {$aFbId} \n";
                                    $image -> file_url = $aImageUrl;
                                    $image -> updated_at = date('Y-m-d H:i:s',time());
                                    $image -> updated_by_id = $this->getBasicAdmin()->id;
                                    $image -> save();
                                }
                            }
                        }elseif($aType == 'video'){
                            echo "Updating post's video information \n";
                            $post->video_key_fb = $aVideoUrl;
                            $post->video_preview_fb = $aImageUrl;
                            $post->video_attachment_id_fb = $aFbId;
                            $post->update();
                        }
                    }
                }

                if($commentsCount > 0){
                    echo "Updating comments... \n";
                    $this->updateComments($post);
                    echo "\n\n\n";
                }

            }

            //should recalculate all user's counters
            /* @var $users User[] */
            echo "Updating users's counters... \n";
            $users = User::find()->all();
            foreach($users as $u){
                $postCount = Post::find()->where(['author_id' => $u->id])->count();
                $commentCount = Comment::find()->where(['author_id' => $u->id])->count();
                $u->counter_comments = $commentCount;
                $u->counter_posts = $postCount;
                $u->update();
            }
            echo "Counters updated. \n";

        }else{
            echo "Can't find any posts related with facebook \n";
        }


        echo "Finished! \n";
    }

    /**
     * Updates users's counters
     * @throws \Exception
     */
    public function actionUpdateCounters()
    {
        echo "Updating users's counters... \n";
        /* @var $users User[] */
        $users = User::find()->all();
        foreach($users as $u){
            $postCount = Post::find()->where(['author_id' => $u->id])->count();
            $commentCount = Comment::find()->where(['author_id' => $u->id])->count();
            $u->counter_comments = $commentCount;
            $u->counter_posts = $postCount;
            $u->update();
            echo "User {$u->id} done. \n";
        }
        echo "Counters updated. \n";
    }

    /**
     * Updates search indices for active (non stock) posts
     */
    public function actionUpdateSearchIndices()
    {
        /* @var $all Post[] */
        echo "Querying all non-stock posts. Please wait...";

        $q = Post::find()->where('status_id != :status',['status' => Constants::STATUS_IN_STOCK]);
        $q ->with(['categories','comments']);
        $count = $q->count();
        $all = $q->all();

        echo "Found ({$count}) posts. Updating search indices... \n";

        foreach($all as $post){
            $post->updateSearchIndices();
            echo "Post {$post->id} with FB ID {$post->fb_sync_id} done. \n";
        }

        echo "Finished! \n";
    }

    /**
     * Updates comments's nesting relations
     * @throws \Exception
     */
    public function actionReRelateComments()
    {
        echo "Querying comments. Please wait... \n";
        /* @var $comments Comment[] */
        $comments = Comment::find()->with('admParent')->all();
        $count = count($comments);

        echo "Found ({$count}) comments. Processing... \n";

        foreach($comments as $index => $comment){
            if(!empty($comment->admParent)){
                echo "Set prent. ";
                $comment->answer_to_id = $comment->admParent->id;
            }else{
                echo "Set no parents. ";
                $comment->answer_to_id = 0;
            }
            $comment->update();
            $nr = $index+1;
            echo "Done {$nr} of {$count} \n";
        }

        echo "Finished! \n";
    }

    /**
     * Clean posts's names from custom dates in commas
     * @param string $lngPrefix
     * @throws \Exception
     */
    public function actionCleanNames($lngPrefix = 'ru')
    {
        /* @var $all Post[] */
        echo "Querying posts. Please wait...";
        $q = Post::find();
        $count = $q->count();
        $all = $q->all();

        echo "Found ({$count}) posts. Cleaning names... \n";

        foreach($all as $index => $post){
            $trl = $post->getATrl($lngPrefix);
            $name = $trl->name;
            $parts = explode('(',$name);

            if(count($parts) > 1){
                $lastIndex = count($parts)-1;
                if(!empty($parts[$lastIndex])){
                    $name = str_replace('('.$parts[$lastIndex],'',$name);
                    $trl->name = $name;
                    $trl->update();
                    echo "Date in commas removed. ";
                }
            }


            $nr = $index + 1;
            echo "Post {$nr} of {$count} done. \n";
        }

        echo "Finished! \n";
    }
}