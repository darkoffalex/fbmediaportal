<?php
namespace app\commands;

use app\helpers\Constants;
use app\helpers\Help;
use app\helpers\Sort;
use app\models\Category;
use app\models\Comment;
use app\models\Post;
use app\models\PostCategory;
use app\models\PostGroup;
use app\models\PostImage;
use app\models\PostImageDB;
use app\models\User;
use app\models\UserTimeLine;
use Yii;
use yii\base\Exception;
use yii\console\Controller;
use app\helpers\AdminizatorApi;
use yii\data\Pagination;
use yii\db\Expression;
use yii\db\IntegrityException;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

class SyncController extends Controller
{
    const WARNING_COUNT = 1000;
    const UTC_TIME_OFFSET = 3600 * 3;


    public $eld = 24;
    public $from = null;
    public $to = null;
    public $output = true;
    public $lng = 'ru';
    public $portion = 50;
    public $parsed = 'no';
    public $stock = 'no';
    public $attachments = 'yes';
    public $mark = 'yes';
    public $order = 'random';
    public $ids = "";

    public $fromCat = 0;
    public $toCat = 0;

    public function options($actionID)
    {
        return ['eld', 'output', 'lng', 'portion', 'parsed', 'stock', 'from', 'attachments', 'to', 'mark', 'order', 'ids', 'fromCat', 'toCat'];
    }

    private $processId = null;

    /**
     * Get or create new post
     * @param array $data
     * @param string $name
     * @param int $status
     * @return Post
     */
    public function getPost($data, $name = null, $status = Constants::STATUS_IN_STOCK)
    {
        /* @var $post Post */
        $post = Post::find()->where(['fb_sync_id' => ArrayHelper::getValue($data,'id')])->one();

        if(empty($post)){
            $post = new Post();
            $post->fb_sync_id = ArrayHelper::getValue($data,'id');
            $post->content_type_id = Constants::CONTENT_TYPE_POST;
            $post->status_id = $status;
            $post->type_id = Constants::POST_TYPE_IMPORTED;

            $name = empty($name) ? StringHelper::truncateWords(strip_tags(ArrayHelper::getValue($data,'content')),3) : $name;
            $post->name = !empty($name) ? $name : 'Не указано';

            $post->published_at = ArrayHelper::getValue($data,'published_time',date('Y-m-d H:i:s', time()));
            $post->updated_at = ArrayHelper::getValue($data,'updated_time',date('Y-m-d H:i:s', time()));
            $post->created_at = date('Y-m-d H:i:s', time());
            $post->created_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
            $post->updated_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
            $post->need_update = (int)true;
        }else{
            $post->updated_at = ArrayHelper::getValue($data,'updated_time',date('Y-m-d H:i:s', time()));
            $post->updated_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
            $post->need_update = (int)true;
        }

        return $post;
    }

    /**
     * Retrieve group by data
     * @param $data
     * @return PostGroup|null
     */
    private function getGroup($data)
    {
        /* @var $group PostGroup */

        $fbId = ArrayHelper::getValue($data,'id');
        $name = ArrayHelper::getValue($data,'name');

        if(!empty($fbId) && !empty($name)){

            $group = PostGroup::find()->where(['fb_sync_id' => $fbId])->one();

            if(empty($group)){
                $group = new PostGroup();
                $group -> fb_sync_id = $fbId;
                $group -> name = $name;
                $group -> url = "https://www.facebook.com/groups/{$fbId}/";
                $group -> is_group = (int)true;
                $group -> stock_enabled = (int)true;
                $group -> stock_sync = (int)true;
                $group -> created_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                $group -> updated_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                $group -> created_at = date('Y-m-d H:i:s',time());
                $group -> updated_at = date('Y-m-d H:i:s',time());
                $group -> save();
            }

            return $group;
        }

        return null;
    }

    /**
     * Retrieve author-user by data
     * @param $data
     * @return User|array|null|\yii\db\ActiveRecord
     */
    private function getAuthor($data)
    {
        $fbId = ArrayHelper::getValue($data,'id');
        $name = ArrayHelper::getValue($data,'name');
        $surname = ArrayHelper::getValue($data,'surname');
        $avatar_url = ArrayHelper::getValue($data,'avatar_url');

        if(!empty($fbId)){
            $user = User::find()->where(['fb_user_id' => $fbId])->one();

            if(empty($user)){
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
                    $user->updated_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                    $user->status_id = Constants::STATUS_ENABLED;
                    $user->role_id = Constants::ROLE_REGULAR_USER;
                    $user->type_id = Constants::USR_TYPE_IMPORTED;
                    $user->save();
                }catch (\Exception $ex){
                    return null;
                }
            }

            return $user;
        }

        return null;
    }

    /**
     * Updating attachments for post
     * @param Post $post
     * @param array $data
     * @param bool $update
     * @param bool $grab
     */
    private function updateAttachments($post, $data, $update = false, $grab = false)
    {
        foreach($data as $attachment){
            $aType = ArrayHelper::getValue($attachment,'type');
            $aFbId = ArrayHelper::getValue($attachment,'id');
            $aImageUrl = ArrayHelper::getValue($attachment,'image_url');
            $aVideoUrl = ArrayHelper::getValue($attachment,'origin_url');

            if($aType == 'photo' || $aType == 'share'){
                /* @var $image PostImage */
                $image = PostImageDB::find()->where(['fb_sync_id' => $aFbId, 'post_id' => $post->id])->one();

                if(empty($image)){
                    $image = new PostImageDB();
                    $image -> fb_sync_id = $aFbId;
                    $image -> is_external = (int)true;
                    $image -> file_url = $aImageUrl;
                    $image -> post_id = $post->id;
                    $image -> status_id = Constants::STATUS_ENABLED;
                    $image -> priority = Sort::GetNextPriority(PostImage::className(),['post_id' => $post->id]);
                    $image -> created_at = date('Y-m-d H:i:s',time());
                    $image -> updated_at = date('Y-m-d H:i:s',time());
                    $image -> created_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                    $image -> updated_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                    $image -> save();
                }elseif($update){
                    if($image->file_url != $aImageUrl){
                        $image -> file_url = $aImageUrl;
                        $image -> updated_at = date('Y-m-d H:i:s',time());
                        $image -> updated_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                        $image -> update();
                    }
                }
            }elseif($aType == 'video'){
                $post->video_key_fb = $aVideoUrl;
                $post->video_preview_fb = $aImageUrl;
                $post->video_attachment_id_fb = $aFbId;
                $post->update();
            }
        }
    }

    /**
     * Updating post's comments
     * @param $post
     * @throws \Exception
     */
    private function updateComments($post)
    {
        /* @var $post Post */
        $meta = [];
        $commentsArr = AdminizatorApi::getInstance()->getComments($post->fb_sync_id,1,$meta);

        if(!empty($commentsArr)){
            for ($i = 1; $i <= $meta['lastPage']; $i++){
                //get comment list
                $commentsArr = AdminizatorApi::getInstance()->getComments($post->fb_sync_id,$i,$meta);

                //pass through all comments
                foreach ($commentsArr as $itemData){

                    //get main fields
                    $fbId = ArrayHelper::getValue($itemData,'id');
                    $sysId = (int)ArrayHelper::getValue($itemData,'system_id');
                    $answerToSysId = (int)ArrayHelper::getValue($itemData,'answer_to_id');
                    $content = ArrayHelper::getValue($itemData,'content');
                    $time = ArrayHelper::getValue($itemData,'published_time');
                    $authorData = ArrayHelper::getValue($itemData,'author');

                    //try to find comment
                    $comment = Comment::find()->where(['fb_sync_id' => $fbId, 'post_id' => $post->id])->one();

                    //if comment not found - create
                    if(empty($comment)){
                        $comment = new Comment();
                        $comment -> post_id = $post->id;
                        $comment -> fb_sync_id = $fbId;
                        $comment -> adm_id = $sysId;
                        $comment -> answer_to_adm_id = $answerToSysId;
                        $comment -> text = $content;
                        $comment -> created_at = $time;
                        $comment -> updated_at = date('Y-m-d H:i:s',time());
                        $comment -> created_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                        $comment -> updated_by_id = AdminizatorApi::getInstance()->basicAdmin->id;

                        //get author and set it
                        $author = $this->getAuthor($authorData);
                        if(!empty($author)){
                            $comment->author_id = $author->id;
                        }

                        //save new comment
                        $saved = $comment->save();

                        //update author's time-line if added new comment for activated post
                        if($saved && !empty($author) && $post->status_id == Constants::STATUS_ENABLED){
                            $utl = new UserTimeLine();
                            $utl -> comment_id = $comment->id;
                            $utl -> user_id = $comment->author_id;
                            $utl -> published_at = $comment->created_at;
                            $utl -> save();
                        }
                    }
                }
            }

            //build relations for internal ID's using external (adminizator's) ID's
            Yii::$app->db->createCommand("UPDATE `comment` c1 SET answer_to_id = (SELECT id FROM (SELECT id, adm_id FROM `comment` WHERE post_id = :post) as c2 WHERE c2.adm_id = c1.answer_to_adm_id LIMIT 1) WHERE c1.post_id = :post",['post' => $post->id])->query();
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Checking for updates (creates new posts, or marks as "need update" already added)
     */
    public function actionIndex()
    {
        //log process start
        $this->processId = Help::rds(10);
        Help::log('updates.log',"Process {$this->processId} started");
        //timezone
        date_default_timezone_set('Europe/Moscow');
        //mysql wait timeout
        Yii::$app->db->createCommand('SET SESSION wait_timeout = 28800;')->execute();
        //unlimited execution time
        set_time_limit(0);

        //time interval
        $timeFrom = empty($this->from) ? date('Y-m-d H:i:s',(time() - (3600 * $this->eld))) : $this->from;
        $timeTo = empty($this->to) ? date('Y-m-d H:i:s',time()) : $this->to;
        if($this->output) echo "Checking interval ({$timeFrom} - {$timeTo})\n";

        //getting groups
        if($this->output) echo "Retrieving groups \n\n";

        /* @var $groups PostGroup[] */
        $groups = PostGroup::find()->where([
            'is_group' => 1,
            'stock_enabled' => 1,
            'stock_sync' => 1]
        )->all();

        //end process if groups not found
        if(empty($groups)){
            Help::log('updates.log',"Process {$this->processId} terminated. No groups found");
            exit($this->output ? "No groups found. Precess terminated \n" : null);
        }

        //build array of ids
        $countGroups = count($groups);
        $groupsIds = [];

        if($this->output) echo "Found {$countGroups} groups:\n";
        foreach ($groups as $index => $group){
            $groupsIds[] = $group->fb_sync_id;
            if($this->output) echo "{$group->fb_sync_id}\n";
            if($this->output && $index == (count($groups)-1)) echo "\n";
        }

        //query all posts
        if($this->output) echo "Retrieving posts\n\n";
        $meta = [];

        //apply UTC offset
        $timeFrom = date('Y-m-d H:i:s',(new \DateTime($timeFrom))->getTimestamp()-self::UTC_TIME_OFFSET);
        $timeTo = date('Y-m-d H:i:s',(new \DateTime($timeTo))->getTimestamp()-self::UTC_TIME_OFFSET);

        $postsItemsArr = AdminizatorApi::getInstance()->getPosts($timeFrom,$timeTo,$groupsIds,1,$meta);

        if(empty($postsItemsArr)){
            Help::log('updates.log',"Process {$this->processId} terminated");
            exit($this->output ? "Nothing found\n" : null);
        }

        //passing through all pages
        if($this->output) echo "Found {$meta['total']} posts. Processing...\n";

        if($meta['total'] > self::WARNING_COUNT){
            Help::log('updates.log',"Process {$this->processId} paused by question");
            $warn = self::WARNING_COUNT;
            if(!$this->confirm("Found more than {$warn} posts ({$meta['total']}). Continue ?")){
                Help::log('updates.log',"Process {$this->processId} terminated");
                exit($this->output ? "Canceled by user\n" : null);
            }
        }


        for ($i = 1; $i <= $meta['lastPage']; $i++){
            $postsItemsArr = AdminizatorApi::getInstance()->getPosts($timeFrom,$timeTo,$groupsIds,$i,$meta);

            //for each item of page
            if(!empty($postsItemsArr)){
                foreach ($postsItemsArr as $postArr){

                    try{
                        //find or create
                        $post = $this->getPost($postArr);
                        $post->translateLabels = false;

                        //if not found - save new
                        if($post->isNewRecord){
                            if($this->output) echo "Post with id {$postArr['id']} not found. Creating...\n";

                            $group = $this->getGroup($postArr['group']);
                            $author = $this->getAuthor($postArr['author']);

                            if(!empty($group)){
                                if($this->output) echo "Group set\n";
                                $post->group_id = $group->id;
                            }else{
                                if($this->output) echo "ERROR! Group not set\n";
                            }

                            if(!empty($author)){
                                if($this->output) echo "Author set\n";
                                $post->author_id = $author->id;
                            }else{
                                if($this->output) echo "ERROR! Author not set\n";
                            }

                            if($post->save()){
                                if($this->output) echo "Post with id {$postArr['id']} saved.\n";

                                if(!empty($postArr['attachments']) && $this->attachments == 'yes'){
                                    if($this->output) echo "Updating attachments...\n";
                                    $this->updateAttachments($post,$postArr['attachments']);
                                }

                                if($this->output) echo "Updating translatable content...\n";
                                $trl = $post->getATrl($this->lng);
                                $trl -> name = $post->name;
                                $trl -> text = strip_tags($postArr['content']);
                                $trl -> small_text = StringHelper::truncateWords($trl->text,20);
                                $trl -> isNewRecord ? $trl->save() : $trl->update();

                                if($this->output) echo "Post with id {$postArr['id']} completed.\n\n";
                            }

                        //if found - mark as 'need update'
                        }else{
                            if($this->output) echo "Post with id {$postArr['id']} already added.\n";

                            if($this->mark == 'yes'){
                                if($this->output) echo "Marking as updating...\n";
                                $post->update();
                            }

                            if($this->output) echo "Post with id {$postArr['id']} completed.\n\n";

                        }
                    }catch (Exception $ex){
                        Help::log('updates.log',"Process {$this->processId} terminated by error");
                        exit($this->output ? "ERROR: {$ex->getMessage()} \n" : null);
                    }
                }
            }
        }

        //log process end
        if($this->output) echo "Done.\n\n";
        Help::log('updates.log',"Process {$this->processId} terminated");
    }

    /**
     * Updates all posts that needs to be updated
     */
    public function actionUpdate()
    {
        //log process start
        $this->processId = Help::rds(10);
        Help::log('updates.log',"Process {$this->processId} started");
        //timezone
        date_default_timezone_set('Europe/Moscow');
        //mysql wait timeout
        Yii::$app->db->createCommand('SET SESSION wait_timeout = 28800;')->execute();
        //unlimited execution time
        set_time_limit(0);


        //build query
        $q = Post::find()->where('status_id != :archived',['archived' => Constants::STATUS_DELETED]);

        //what kind of posts we search for (parsed, parsed and awaiting updates, parsed only, or ignoring parsed)
        switch ($this->parsed){
            case 'no':
                $q->andWhere(['need_update' => 1]);
                $note = 'ALL';
                break;
            case 'yes':
                $q->andWhere(['need_update' => 1, 'is_parsed' => 1]);
                $note = 'PARSED';
                break;
            case 'only':
                $q->andWhere(['is_parsed' => 1]);
                $note = 'PARSED ONLY';
                break;
            case 'ignore':
                $q->andWhere(new Expression('is_parsed = 0 OR is_parsed IS NULL'))->andWhere(['need_update' => 1]);
                $note = 'IGNORE PARSED';
                break;
            default:
                $q->andWhere(['need_update' => 1]);
                $note = 'ALL';
                break;
        }

        //if stock items only
        if($this->stock == 'yes'){
            $q->andWhere(['status_id' => Constants::STATUS_IN_STOCK]);
            $note.= "|STOCK ONLY";
        }

        /* @var $posts Post[] */
        switch ($this->order){
            case 'random':
            case 'RAND':
                $order = new Expression('RAND()');
                $note.="|RANDOM";
                break;
            case 'asc':
            case 'ASC':
                $order = new Expression('published_at ASC');
                $note.="|ASC";
                break;
            case 'desc':
            case 'DESC':
                $order = new Expression('published_at DESC');
                $note.="|DESC";
                break;
            default:
                $order = new Expression('RAND()');
                $note.="|RANDOM";
        }

        //get all posts which should be updated
        if($this->output) echo "Querying posts ({$note})\n\n";

        $posts = $q->orderBy($order)->limit($this->portion)->all();

        if(empty($posts)){
            Help::log('updates.log',"Process {$this->processId} terminated");
            exit($this->output ? "Nothing found\n" : null);
        }

        $count = count($posts);
        if($this->output) echo "Found {$count} posts (potion size {$this->portion})\n\n";

        //update all posts
        foreach ($posts as $post){

            try{
                //get details for this post
                if($this->output) echo "Processing {$post->fb_sync_id} post \n";
                $details = AdminizatorApi::getInstance()->getDetails($post->fb_sync_id);

                //disable translatable labels
                $post->translateLabels = false;

                //if found some data
                if(!empty($details)){

                    //update attachments if needed
                    if(!empty($details['attachments'])){
                        if($this->output) echo "Updating attachments\n";
                        $this->updateAttachments($post,$details['attachments']);
                    }

                    //update author if needed
                    if(empty($post->author_id)){
                        if($this->output) echo "Setting author\n";
                        $author = $this->getAuthor($details['author']);
                        if(!empty($author)){
                            if($this->output) echo "Author set\n";
                            $post->author_id = $author->id;
                        }else{
                            if($this->output) echo "ERROR! Author not set\n";
                        }
                    }else{
                        if($this->output) echo "Author already set\n";
                    }

                    //update group if needed
                    if(empty($post->group_id)){
                        $group = $this->getGroup($details['group']);
                        if(!empty($group)){
                            if($this->output) echo "Group set\n";
                            $post->group_id = $group->id;
                        }else{
                            if($this->output) echo "ERROR! Group not set\n";
                        }
                    }else{
                        if($this->output) echo "Group already set\n";
                    }

                    //update content if post still in stock
                    if($post->status_id == Constants::STATUS_IN_STOCK){
                        if($this->output) echo "Updating translatable content\n";

                        $name = StringHelper::truncateWords(strip_tags(ArrayHelper::getValue($details,'content')),3);
                        $post->name = $post->is_parsed ? $post->name : (!empty($name) ? $name : 'Не указано');

                        $trl = $post->getATrl($this->lng);
                        $trl -> name = $post->name;
                        $trl -> text = strip_tags(ArrayHelper::getValue($details,'content'));
                        $trl -> small_text = StringHelper::truncateWords($trl->text,20);
                        $trl -> isNewRecord ? $trl->save() : $trl->update();

                        if($post->is_parsed){
                            if($this->output) echo "Updating search keywords\n";
                            $post->updateSearchKeywords();
                        }
                    }

                    //update comments
                    if($this->output) echo "Updating comments\n";
                    $this->updateComments($post);

                    //finalize updating
                    $post->published_at = ArrayHelper::getValue($details,'published_time',date('Y-m-d H:i:s', time()));
                    $post->updated_at = ArrayHelper::getValue($details,'updated_time',date('Y-m-d H:i:s', time()));
                    $post->created_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                    $post->updated_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                    $post->need_update = (int)false;

                    //if working with parsed - move to common list
                    if($post->is_parsed){
                        $post->status_id = Constants::STATUS_ENABLED;
                    }

                    $post->update();
                }else{
                    if($this->output) echo "No data found for post {$post->fb_sync_id}\n";
                }

                if($this->output) echo "Post {$post->fb_sync_id} (ID {$post->id}) done.\n\n";

            }catch (Exception $ex){
                Help::log('updates.log',"Process {$this->processId} terminated by error");
                exit($this->output ? "ERROR: {$ex->getMessage()} \n" : null);
            }
        }

        try{
            //update all counters
            $this->actionUpdateCounters();
        }catch (Exception $ex){
            Help::log('updates.log',"Process {$this->processId} terminated by error");
            exit($this->output ? "ERROR: {$ex->getMessage()} \n" : null);
        }

        //log process end
        if($this->output) echo "Done.\n\n";
        Help::log('updates.log',"Process {$this->processId} terminated");
    }

    /**
     * Updates counters
     * @throws \Exception
     */
    public function actionUpdateCounters()
    {
        $db = Yii::$app->db;

        if($this->output) echo "Updating post's counters... \n";
        $db->createCommand("UPDATE post p SET p.comment_count = (SELECT COUNT(*) FROM `comment` WHERE `comment`.post_id = p.id), last_comment_at = (SELECT created_at FROM `comment` ORDER BY created_at LIMIT 1)")->query();

        if($this->output) echo "Updating users's counters... \n";
        $db->createCommand("UPDATE `user` u SET counter_comments = (SELECT COUNT(*) FROM `comment` WHERE `comment`.author_id = u.id), counter_posts = (SELECT COUNT(*) FROM post WHERE post.author_id = u.id)")->query();

        if($this->output) echo "Counters updated. \n";
    }

    /**
     * Updates comments's nesting relations
     * @throws \Exception
     */
    public function actionReRelateComments()
    {
        echo "Querying posts. Please wait... \n";
        /* @var $posts Post[] */
        $posts = Post::find()->all();
        $count = count($posts);

        echo "Found ({$count}) posts. Updating comment relations... \n";

        foreach($posts as $index => $post){
            Yii::$app->db->createCommand("UPDATE `comment` c1 SET answer_to_id = (SELECT id FROM (SELECT id, adm_id FROM `comment` WHERE post_id = :post) as c2 WHERE c2.adm_id = c1.answer_to_adm_id LIMIT 1) WHERE c1.post_id = :post",['post' => $post->id])->query();
            $nr = $index+1;
            echo "Done {$nr} of {$count} \n";
        }

        echo "Finished! \n";
    }

    /**
     * Updates search indices for active (non stock) posts
     */
    public function actionUpdateSearchIndices()
    {
        /* @var $all Post[] */
        echo "Querying all non-stock posts. Please wait...";

        $q = Post::find()->where('status_id != :status',['status' => Constants::STATUS_IN_STOCK]);
        $count = $q->count();

        echo "Found ({$count}) posts. Updating search indices... \n";

        $pages = new Pagination(['totalCount' => $q->count(), 'defaultPageSize' => 20]);
        for ($i = 0; $i <= $pages->pageCount; $i++){
            $pages->setPage($i);

            /* @var $posts Post[] */
            $posts = $q->with(['categories','comments'])
                ->limit($pages->limit)
                ->offset($pages->offset)->all();

            foreach($posts as $index => $post){
                $currentIndex = (($pages->page) * $pages->limit) + $index;
                $post->updateSearchKeywords();
                echo "Post {$currentIndex} of {$count} done. \n";
            }
        }

        echo "Finished! \n";
    }

    /**
     * Updates activity time-lines for all users
     */
    public function actionUpdateUserTimeLines()
    {        /* @var $all Post[] */
        echo "Querying all users. Please wait...\n";

        $q = User::find();

        if(!empty($this->ids)){
            $IDs = explode(',',$this->ids);
            $q->andWhere(['id' => $IDs]);
        }

        $count = $q->count();

        if(!$this->confirm("Found ({$count}) users. Do you want proceed ?\n")){
            echo "Canceled. \n";
            exit();
        }

        echo "Updating...\n";

        $pages = new Pagination(['totalCount' => $count, 'defaultPageSize' => 20]);
        for ($i = 0; $i <= $pages->pageCount; $i++){
            $pages->setPage($i);

            /* @var $users User[] */
            $users = $q->limit($pages->limit)->offset($pages->offset)->all();

            foreach($users as $index => $user){
                $currentIndex = (($pages->page) * $pages->limit) + $index + 1;
                $user->refreshTimeLine();
                echo "User {$currentIndex} of {$count} done. \n";
            }
        }

        echo "Done!\n";
    }

    /**
     * Fix priorities in all categories (if done mistake and same value was set for several items)
     * @param int $rootId
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionFixPriorities($rootId = 0)
    {
        echo "Querying all categories of root {$rootId}. Please wait...\n";

        /* @var $categories Category[] */
        $categories = Category::find()->where(['parent_category_id' => $rootId])->orderBy('priority ASC')->all();
        $total = count($categories);

        foreach($categories as $index => $cat){
            $cat->priority = $index+1;
            $cat->update();

            $current = $index + 1;

            if(Category::find()->where(['parent_category_id' => $cat->id])->count() > 0){
                $this->actionFixPriorities($cat->id);
            }

            echo "Category {$current} of {$total} [{$cat->id}] updated. Priority set to - {$current} \n";
        }
    }

    /**
     * Fixes empty names (when has internal name, but tranlatable not set)
     * @param int $rootId
     * @param string $lng
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionFixCategoryNames($rootId = 0, $lng = 'ru'){
        /* @var $categories Category[] */
        $categories = Category::find()->where(['parent_category_id' => $rootId])->orderBy('priority ASC')->all();

        foreach($categories as $index => $cat){
            $trl = $cat->getATrl($lng);

            if(empty($trl->name) && !empty($cat->name)){
                $trl->name = $cat->name;
                $trl->isNewRecord ? $trl->save() : $trl->update();
                echo "Category [{$cat->id}] fixed. Name set to - {$trl->name} \n";
            }

            if(Category::find()->where(['parent_category_id' => $cat->id])->count() > 0){
                $this->actionFixCategoryNames($cat->id,$lng);
            }
        }
    }

    /**
     * Re attaches category content to another category
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\console\Exception
     */
    public function actionReAttachPosts()
    {
        if(empty($this->fromCat) || empty($this->toCat)){
            throw new \yii\console\Exception("Please specify 'fromCat' and 'toCat' values");
        }

        /* @var $pcs PostCategory[] */
        $pcs = PostCategory::find()->where(['category_id' => (int)$this->fromCat])->all();

        if(!empty($pcs)){

            $count = count($pcs);
            if(!$this->confirm("Found {$count} items for movement. Continue ?")){
                echo "Canceled by user \n";
                echo "Done! \n";
                exit();
            }

            foreach($pcs as $pc){
                $alreadyAdded = PostCategory::find()->where(['category_id' => (int)$this->toCat, 'post_id' => $pc->post_id])->one();
                if(empty($alreadyAdded)){
                    $pc->category_id = (int)$this->toCat;
                    $pc->updated_at = date('Y-m-d H:i:s',time());
                    $pc->update();
                    echo "({$pc->post_id}) - Post moved to category '{$pc->category->name}' \n";
                }else{
                    $pc->delete();
                    echo "({$pc->post_id}) - Target cat already has this post. Deleted from source cat \n";
                }
            }
        }else{
            echo "Nothing found. \n";
        }

        echo "Done! \n";
    }


    /**
     * Fast way to parse not parset shit
     */
    public function actionPostParse()
    {
        $vnz = "Возможность оплаты штрафа за ВНЖ на территории Турции. (02.02.2017)||1465526173480973
Что делать, если пропустили собеседование на ВНЖ? (01.02.2017) ||1464020683631522
Что делать, если аннулирован ВНЖ? (30.01.2017) ||1461750293858561
Сумма налога для 3-х летнего семейного ВНЖ (30.01.2017) ||1460991100601147
О соответствии срока действий страховок и двухгодичного ВНЖ (30.01.2017) ||1461754053858185
Сумма затрат на получение туристического ВНЖ (30.01.2017) ||1461869343846656
Распечатка анкеты из сайта иммиграционной службы (27.01.2017) ||1458082650891992
Сроки отсутствия из Турции после получения ВНЖ (25.01.2017) ||1456131221087135
Действия при утере карточки ВНЖ (18.01.2017) ||1447596091940648
Онлайн заполнение анкеты первичного обращения для получения ВНЖ (18.01.2017) ||1447527528614171
Выезд из Турции в период продления ВНЖ (15.01.2017) ||1444798262220431
Оформления ВНЖ туркам в РФ (14.01.2017) ||1443938175639773
Оплата при онлайн заполнении анкеты на продление ВНЖ (14.01.2017) ||1443582709008653
Сколько можно отсутствовать из Турции после получения ВНЖ (11.01.2017) ||1440430102657247
Подача на ВНЖ без посреднических фирм (11.01.2017) ||1440497962650461
Как проверить действительность рандеву на продление ВНЖ по интернету (10.01.2017) ||1439553746078216
Порядок продления ВНЖ на два года (10.01.2017) ||1439675232732734
О возможных изменениях в выдаче туристического ВНЖ (10.01.2017) ||1439512539415670
Сроки доставки карточки ВНЖ домой после смс уведомления (05.01.2017) ||1434430786590512
Необходимо ли предъявить документы о материальных средствах при продлении ВНЖ (09.12.2016) ||1438615322838725
Есть ли ограничения на выезд из Турции при семейном ВНЖ (18.12.2016) ||1411468888886702
Поиск дешевой страховки для продления ВНЖ (18.12.2016) ||1411678552199069
Требуется ли уведомление УФМС о получении ВНЖ (18.12.20160) ||1411458502221074
Поиск дешевой страховки для продления ВНЖ (14.12.2016) ||1405865539447037
Порядок выезда из Турции в периоды продлевания и ожидания рандеву (11.12.2016) Подробнее…
Установленные сроки пребывания вне Турции в период продлевания ВНЖ (11.12.2016) Подробнее…
Сбои в системе Е-randevu миграционной службы, как быть? (07.12.2016) ||1395638233803101
Можно ли переносить дату рандеву для ВНЖ (29.11.2016) ||1385150168185241
Нужен ли нотариально заверенный договор аренды при первичной подаче на семейный ВНЖ (27.11.2016) ||1383295731704018
Возможно ли получить ВНЖ на себя и своего ребенка по недвижимости в Турции, принадлежащей матери (23.11.2016) ||1378865628813695
Есть ли преимущества пригона своего авто владельцам ВНЖ (23.11.2016) ||1378865628813695
Требуется ли встать на учет УФМС россиянам, проживающим по ВНЖ в Турции (19.11.2016) ||1374627685904156
Документы, требующиеся для ВНЖ несовершеннолетнего (19.11.2016) ||1374198135947111
Получения турецкого гражданства детям от первых браков в РФ (19.11.2016) ||1374116312621960
Как показать движение средств по счету для ВНЖ? (14.11.2016) ||1369373546429570
Что дешевле и быстрее — оформление туристического икамета. и изменение его статуса на семейный или получение семейного после регистрации? (14.11.2016) ||1369017686465156
Нужна помощь по оформлению ВНЖ в Анталии (13.11.2016) ||1367905263243065
Нужна ли справка из банка для ВНЖ? Какой адрес указывать в анкете? (09.11.2016) ||1363141913719400
Нужна помощь по оформлению справки в банке для продления ВНЖ (07.11.2016) ||1361536923879899
Прошу совета по подаче заявки на ВНЖ онлайн (06.11.2016) ||1360435487323376
Проблемы при оформлении заявки на продление икамета онлайн (06.11.2016) ||1360521553981436
Какие финансовые документы нужны для продления ВНЖ? (03.11.2016) ||1357849507581974
Через сколько лет после получения ВНЖ по недвижимости можно подавать на гражданство? Необходимо жить в этой квартире? (01.11.2016) ||1355569707809954
Как оформить икамет недорого? (01.11.2016) ||1355402007826724
Как уведомить российские власти о двойном гражданстве? (01.11.2016) ||1355245284509063
Можно ли оформить ВНЖ без брака и без приглашения работодателя? (01.11.2016) ||1355240087842916
Можно ли вылететь из Турции, если икамет находится на продлении? (31.10.2016) ||1354423844591207
Можно ли продлить икамет по замужеству без присутствия мужа? (30.10.2016) ||1353473768019548
В какой срок после окончания рабочей визы надо покинуть страну? Сколько дней оформляется ВНЖ по замужеству? (30.10.2016) ||1353344661365792
Даёт ли право на гражданство и на работу приобретение недвижимости в Турции (28.10.2016) ||1351577201542538
Через какой период можно въезжать обратно при аннулировании ВНЖ (27.10.2016) ||1350656618301263
Где недорого купить страхование для ВНЖ (26.10.2016) ||1349292228437702
Существуют ли нормативные документы для банков о прекращении операций в случае прекращения действия ВНЖ (25.10.2016) ||1348439291856329
Можно ли уведомлять УФМС о виде на жительство почтой (24.10.2016) ||1347460855287506
Как отстоять право на продление ВНЖ с дальнейшей подачей на гражданство, если проблема возникла по вине фирмы-посредника (22.10.2016)||1345473055486286
Сроки получения икамета при продлении ВНЖ (23.10.2016) ||1346172268749698
Полицейские проверки в процессе получения ВНЖ (22.10.2016) ||1345301945503397
Поиск услуг по сбору документов на ВНЖ (22.10.2016) ||1345192552181003
Помогает ли консульство РФ в вопросах продления просроченных ВНЖ (21.10.2016) ||1344319638934961
Можно ли взять рандеву на продление ВНЖ не в Анталии, а в Кемере? (18.10.2016) ||1341432075890384
Нужен ли счет в банке для оформления икамета? (17.10.2016) ||1340167042683554
Можно ли выезжать из страны до рандеву (после окончания икамета)? (16.10.2016) ||1339100446123547
Нужна ли справка из банка при уведомлении о смене адреса? (16.10.2016) ||1339042649462660
Сколько длится процедура развода по обоюдному согласию? Может ли муж аннулировать семейный ВНЖ, если жена находится в другой стране? (15.10.2016) ||1338240646209527
Нужна ли справка из банка при оформлении первичного ВНЖ? (13.10.2016) ||1335976843102574
Где сделать ВНЖ и сколько это стоит? (13.10.2016) ||1335626749804250
Какие документы нужны для оформления семейного икамета поле замужества? (11.10.2016) ||1333080400058885
Информация об оформлении икамета при переезде (06.10.2016) ||1328924410474484
Сколько времени оформляют икамет? (06.10.2016) ||1329811567052435
Как быстро и правильно продлить ВНЖ? (30.09.2016) ||1322860594414199
Поиск расценок на сигорты (22.09.2016) ||1315458711821054
Правда ли,что для оформления ВНЖ больше не нужна справка о средствах на счете? (22.09.2016) ||1315063788527213
Где в Ларе сделать страховку для ВНЖ? (22.09.2016) ||1314987301868195
Как оформить удаленный договор бесплатного проживания для икамета? (22.09.2016) ||1315307475169511
Где в Ларе сделать страховку для ВНЖ? (22.09.2016) ||1314987301868195
Надо ли детям делать страховку при продлении отурмы? (21.09.2016) ||1314105618623030
Почему не заполняется форма на продление семейного икамета? (21.09.2016) ||1315023405197918
Где менять адрес по визе при переезде из Анталии в Измир? (20.09.2016) ||1312675922099333
Как самостоятельно продлить отурму? (19.09.2016) ||1312452565455002
Аннулирован икамет раньше срока, что делать? (19.09.2016) ||1312188195481439
Как сделать справку из банка и сколько это стоит? (16.09.2016) ||1309241159109476
Какие документы нужны для оформления семейного икамета и сколько он действует? (16.09.2016) ||1309264472440478
Когда продлевать икамет и какие санкции налагаются на опоздавших? (09.09.2016) ||1302143783152547
Как зайти на страницу резервации рандеву и проверить правильность оформления заявки? (08.09.2016) ||1301081046592154
Сколько будет стоить отурма? (07.09.2016) ||1300100133356912
В каких случаях отказывают в выдаче икамета? (06.09.2016) ||1299581730075419
Не получается подать заявку на продление икамета онлайн (06.09.2016) ||1299695880064004
Можно ли уже подавать документы на двухгодовой ВНЖ? (03.09.2016) ||1296481070385485
Как происходит и сколько стоит продление ВНЖ? (01.09.2016) ||1294347043932221
Задержка в получении икамета (29.08.2016) ||1291452477555011
Можно ли заявление подать на продление икамета через интернет? (29.08.2016) ||1291465584220367
Нужно ли платить налоги в РФ, если вы живете по ВНЖ и работаете в Турции? (26.08.2016) ||1288580811175511
Сколько времени делают ВНЖ? (24.08.2016) ||1286251111408481
Нужно ли для продления икамета по замужеству заверять копию паспорта? (23.08.2016)
Можно ли сделать ВНЖ на 2 года? Что дает семейный ВНЖ? (20.08.2016) ||1282063101827282
Куда обратиться для оформления ВНЖ? (19.08.2016) ||1281178658582393
Статья об увеличении сроков действия ВНЖ (18.08.2016) ||1279825628717696
Изменения в процедуре оформления ВНЖ (17.08.2016) ||1278888458811413
Как продлить икамет, если истекает срок действия паспорта? (12.08.2016) ||1274298359270423
Проблемы с документами при регистрации брака и продления ВНЖ (12.08.2016) ||1274298359270423
Нужно ли заверять договор аренды для получения ВНЖ? (07.08.2016) ||1266458590054400
Может ли супруга получить карточку ВНЖ за мужа на почте? (29.07.2016) ||1262130220487237
Порядок выезда за границу в период ожидания ВНЖ (28.07.2017) ||1261676840532575
Сроки нахождения за пределами Турции для владельцев туристического ВНЖ (26.07.2016) ||1260161930684066
Можно ли заполнить анкету на продление за месяц до окончания икамета? (22.07.2016) ||1257142320986027
Допустимые сроки пребывания в Турции при просроченном ВНЖ (02.07.2016) ||1241749502525309
Как  срочно получить рандеву для продления икамета? (01.07.2016) ||1241468115886781
Отзывы о сайте продления ВНЖ (30.06.2016) ||1240951929271733
Отзывы о работе иммиграционной службы Алании (30.06.2016) ||1240767515956841
Смена фамилии на карточке ВНЖ (29.06.2016) ||1240047562695503
Как выезжать за пределы Турции в период ожидания при продлении ВНЖ на новый срок (28.06.2016) ||1239363549430571
Правила пребывания в Турции граждан Беларуси (22.06.2016) ||1235080813192178
Выезд из страны после смены адреса проживания (22.06.2016) ||1235146083185651
Обязательно ли приходить на рандеву по назначенному адресу (16.06.2016) ||1230768926956700
Отзывы о результатах самостоятельного продления ВНЖ (15.06.2016) ||1230178247015768
Можно ли вписать пожилых родителей в свою гос.страховку-SGK (09.06.2016) ||1225899017443691
Банковский счет при продлении ВНЖ — достаточно наличия денег или требуется движение средств в нем? (09.06.2016) ||1225848667448726
Нужно ли аннулировать анкету на продление ВНЖ, если при заполнении допущены ошибки? (03.06.2016) ||1222139961152930
Поиск фирмы для оформления банковского счета для продления ВНЖ (03.06.2016) ||1221900491176877
Заполнение формы на продление ВНЖ (02.06.2016) ||1221095667924026
Банковский счет, рандеву при продлении ВНЖ (01.06.2016) ||1220787314621528
Поиск агенства для оформления туристического ВНЖ (15.05.2016) ||1209185975781662
Оформление семейного ВНЖ (13.05.2016) ||1207444139289179
Где легче и быстрее делать ВНЖ, в Стамбуле или в Анталии? (11.05.2016) ||1205957456104514
Порядок оформления ВНЖ по окончании визы (11.05.2016) ||1205950202771906
Стоимость услуг по оказанию помощи в оформлении ВНЖ (11.05.2016) ||1205965796103680
Правила приема документов на продление семейного ВНЖ (10.05.2016) ||1206159009417692
Полицейские проверки при получении туристического ВНЖ (07.05.2016) ||1202919276408332
Смена адреса — как быть с продлением ВНЖ? (04.05.2016) ||1201266993240227
Есть ли сайт, где можно проследить оформление ВНЖ? (29.04.2016) ||1198036130229980
Сроки выезда из Турции при окончании ВНЖ (29.04.2016) ||1198053110228282
Заполнение документов на продление ВНЖ (27.04.2016) ||1196813907018869
Оповещение Гёч идареси о смене адреса (27.04.2016) ||1196228177077442
Проверки по месту жительства при получении ВНЖ (26.04.2016) ||1195947360438857
Как срочно сделать ВНЖ на год? (24.04.2016) ||1194281577272102
Истекает ли срок ВНЖ из-за долгого отсутствия в стране? (21.04.2016) ||1192537994113127
Сбои в работе сайта Е-ikamet (18.04.2016) ||1190513750982218
Ошибки при выполнении анкеты на продление ВНЖ (18.04.2016) ||1190517167648543
Как получить ВНЖ по недвижимости? (16.04.2016) ||1189346527765607
Сколько ждать икамет? (14.04.2016) ||1187625657937694
Как оформляется вторичный икамет (13.04.2016) ||1186610038039256
Сроки ожидания ВНЖ (13.04.2016) ||1186614594705467
Штрафы при просрочке ВНЖ на 5 месяцев (10.04.2016).||1184782764888650
К кому обратиться по поводу ВНЖ? (07.04.2016) ||1182721878428072
Выезд из страны в ожидании рандеву (06.04.2016) ||1181715755195351
Как проследить путь икамет в PTT? (06.04.2016) ||1182341811799412
Не работает оплата онлайн и коды по системе е-икамет (06.04.2016) ||1182047121828881
Продлят ли отурму, если заканчивается срок действия загранпаспорта? (06.04.2016) ||1181724648527795
Как сделать справку из банка для оформления ВНЖ? (05.04.2016) ||1181348301898763
Поиск специалистов по оформлению ВНЖ в Кемере (04.04.2016) ||1180560875310839
Смс-оповещения о продвижении карточки ВНЖ (30.03.2016) ||1707182406226223
Какие документы нужны ребенку для ВНЖ, если отец указан со слов матери? (29.03.2016) ||1175246715842255
Что делать при просрочке визы или ВНЖ (видеоконсультация) (29.03.2016) ||1174269689273291
Можно ли оформить ВНЖ не выезжая из Турции? (29.03.2015) ||944975565536039
За сколько дней до окончания отурмы нужно подавать на продление? (18.03.2016) ||1163055130394747
Поиск агентств по оформлению ВНЖ (17.03.2016) ||1162310017135925
Где срочно оплатить рандеву? (16.03.2016) ||1161632727203654
Как продлить пребывание в Турции более чем на 60 дней? (15.03.2016) ||1160711360629124
Как отменить заполненную анкету на продление икамета? (15.03.2016) ||1160994283934165
Что будет, если уехать по истечении срока икамета? (14.03.2016) ||1160270684006525
Какие документы нужны для визы по аренде жилья? (13.03.2016) ||1159457147421212
Продление ВНЖ по замужеству — могут ли депортировать при просрочке? (12.03.2016) ||1158774854156108
Можно ли сдать документы на тур. икамет позже срока? (11.03.2016) ||1157996247567302
Задержка в получении ВНЖ, стоит ли беспокоиться? (11.03.2016) ||1158023100897950
Нужно ли уведомлять ФМС России о получении икамета? (11.03.2016) ||1158087697558157
Когда ставить печать после подачи документов на продление? (10.03.2016) ||1157475807619346
Поиск фирм по оформлению ВНЖ (25.02.2016) ||1148356435197950
Нужно ли уведомлять УФМС РФ о ВНЖ в Турции? (18.02.2016) ||1144221098944817
Перечень документов для продления ВНЖ по замужеству (16.02.2016) ||1142816579085269
ВНЖ для турецкого мужа в России (16.02.2016) ||1142758565757737
Основания для ВНЖ (13.02.2016) ||1141107229256204
Где делают ВНЖ? (08.02.2016) ||1137966596236934
Где получить информацию о ВНЖ? (08.02.2016) ||1137933029573624
Кто поможет оформить ВНЖ? (06.02.2016) ||1136965803003680
Информация о получении икамета (03.02.2016) ||1135254996508094
За какой срок необходимо подать документы для продления ВНЖ? (03.02.2016) ||1134976189869308
Как сделать ВНЖ с минимальными затратами? (02.02.2016) ||1134368479930079
Допустимый срок нахождения вне Турции для граждан Казахстана (при наличии ВНЖ) (01.02.2016) ||1133921186641475
Поиск надежной фирмы для оформления ВНЖ (01.02.2016) ||1133923103307950
Делают ли годовое отурма изин при истекающем сроке паспорта? (05.10.2015) ||1071561866210741
Условия получения ВНЖ не по замужеству (04.10.2015) ||1071348212898773
Какие нужны сумма в банке и документы для продления ВНЖ? (02.10.2015) ||1070992339601027
Где платить за икамет и пошлину? (02.10.2015) ||1070516092981985
Где можно узнать полный список документов для ВНЖ? (21.09.2015) ||1065099300190331
За какой срок следует подавать документы на ВНЖ? (21.09.2015) ||1065062723527322
Продление икамета по замужеству (17.09.2015) ||1063008710399390
Пошаговая инструкция для подачи документов для ВНЖ онлайн (16.09.2015) ||1062333473800247
Продление ВНЖ он-лайн (06.09.2015) ||1056632727703655
Делают ли ВНЖ по длительному лечению? (28.08.2015) ||1052081251492136
Какие вопросы задают на рандеву при подаче на ВНЖ? (23.08.2015) ||1049404725093122
Какова процедура получения отурмы после рандеву? (13.07.2015) ||1002674649766130
Как сделать гостевую отурму? (12.07.2015) ||1002261139807481
Где сделать сигорту для икамета? (12.07.2015) ||1001885106511751
Заполнение онлайн-анкеты на оформление ВНЖ (07.07.2015) ||999891510044444
Где оплачивают икамет? (04.07.2015) ||995695763797352
Какие документы подавать на ВНЖ по замужеству? (02.07.2015) ||994795060554089
Изменение адреса проживания во время получения икамета (12.06.2015) ||981834275183501
Список документов, необходимых для получения ВНЖ (11.06.2015) ||981333905233538
Статья о получении ВНЖ по новому закону (11.06.2015) ||981215178578744
Где взять справки для продления икамета? (07.06.2015) ||979629032070692
Как подавать документы на продление икамета? (06.06.2015) ||978805852153010
Как отследить готовность ВНЖ? (20.05.2015) ||971293482904247
Инструкция по получению рандеву на ВНЖ онлайн (20.05.2015) ||971006256266303
Получение рандеву для подачи документов на ВНЖ (18.05.2015) ||970288056338123
Обсуждение разных страховок для ВНЖ (18.05.2015) ||970416856325243
Процедура оформления икамета в Турции (17.05.2015) ||969892956377633
Выезд из страны по истечении срока ВНЖ (13.05.2015) ||968007536566175
Требуются ли счета на воду и электричество при оформлении ВНЖ? (06.05.2015) ||964974190202843
Когда и на какой срок можно продлить ВНЖ? (04.04.2015) ||948199031880359
Можно ли сделать ВНЖ не выезжая из Турции? (29.03.2015) ||944975565536039
Где сделать медицинскую страховку для ВНЖ? (13.03.2015) ||935715013128761
За какой срок до окончания действующего ВНЖ надо подавать документы для продления? (10.03.2105) ||933824006651195
В какой валюте показывать счет при оформлении ВНЖ? (09.03.2015) ||933444986689097
Документы для ВНЖ по аренде (05.03.2015) ||930904106943185
Медицинская страховка для ВНЖ (04.03.2015) ||930667190300210
Как сделать ВНЖ родственнику на 6 или 12 месяцев? (10.02.2015) ||918549461511983
Аннулирование ВНЖ у иностранцев (05.02.2015) ||915935425106720
На какой срок можно покидать страну во время оформления икамета? (24.01.2015) ||909823212384608
Как получить ВНЖ в Анталии? (26.09.2014) ||838926422807621";

        $items = explode("\n",$vnz);
        $itemsPrepared = [];

        foreach($items as $index => $str){
            $tmpArr = explode('||',$str);
            $itemsPrepared[$index]['id'] = !empty($tmpArr[1]) ? (int)$tmpArr[1] : null;
            $itemsPrepared[$index]['name'] = !empty($tmpArr[0]) ? $this->clearName($tmpArr[0]) : null;
        }

        foreach($itemsPrepared as $pit){
            if(!empty($pit['id'])){

                //declaring the post
                $post = null;

                echo "Querying data for {$pit['id']} \n";
                $postArr = AdminizatorApi::getInstance()->getDetails($pit['id']);

                if(!empty($postArr)){
                    echo "Data found. Updating \n";

                    //find or create
                    $post = $this->getPost($postArr,$pit['name'],Constants::STATUS_DISABLED);
                    $post->translateLabels = false;
                    $post->need_update = (int)false;

                    //if not found - save new
                    if($post->isNewRecord){
                        echo "Post with id {$postArr['id']} not found. Creating...\n";

                        $group = $this->getGroup($postArr['group']);
                        $author = $this->getAuthor($postArr['author']);

                        if(!empty($group)){
                            echo "Group set\n";
                            $post->group_id = $group->id;
                        }else{
                            echo "ERROR! Group not set\n";
                        }

                        if(!empty($author)){
                            echo "Author set\n";
                            $post->author_id = $author->id;
                        }else{
                            echo "ERROR! Author not set\n";
                        }

                        if($post->save()){
                            echo "Post with id {$postArr['id']} saved.\n";

                            if(!empty($postArr['attachments']) && $this->attachments == 'yes'){
                                echo "Updating attachments...\n";
                                $this->updateAttachments($post,$postArr['attachments']);
                            }

                            echo "Updating translatable content...\n";
                            $trl = $post->getATrl($this->lng);
                            $trl -> name = $post->name;
                            $trl -> text = strip_tags($postArr['content']);
                            $trl -> small_text = StringHelper::truncateWords($trl->text,20);
                            $trl -> isNewRecord ? $trl->save() : $trl->update();

                            echo "Post with id {$postArr['id']} completed.\n\n";
                        }
                    }else{
                        echo "Post with id {$postArr['id']} already added.\n";
                    }

                }else{

                    echo "Data not found. Creating empty post in stock \n";

                    $post = new Post();
                    $post->translateLabels = false;
                    $post->fb_sync_id = $pit['id'];
                    $post->name = $pit['name'];
                    $post->created_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                    $post->updated_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                    $post->created_at = date('Y-m-d H:i:s',time());
                    $post->updated_at = date('Y-m-d H:i:s',time());
                    $post->status_id = Constants::STATUS_IN_STOCK;
                    $post->content_type_id = Constants::CONTENT_TYPE_POST;
                    $post->type_id = Constants::POST_TYPE_IMPORTED;
                    $post->is_parsed = (int)true;
                    $post->need_update = (int)true;
                    $post->save();

                    echo "Updating translatable content...\n";
                    $trl = $post->getATrl($this->lng);
                    $trl -> name = $post->name;
                    $trl -> isNewRecord ? $trl->save() : $trl->update();
                }

                if(!empty($post)){

                    try{
                        echo "Relating with category \n";
                        $cp = new PostCategory();
                        $cp->post_id = $post->id;
                        $cp->category_id = 438;
                        $cp->created_at = date('Y-m-d H:i:s', time());
                        $cp->updated_at = date('Y-m-d H:i:s', time());
                        $cp->created_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                        $cp->updated_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                        $cp->save();
                    }catch (IntegrityException $ex){
                        echo "Already related \n";
                    }


                    echo "Post with id {$postArr['id']} completed.\n\n";
                }


            }
        }
    }

    /**
     * Cleans fucked names
     * @param $name
     * @return mixed
     */
    private function clearName($name)
    {
        $parts = explode('(',$name);
        $lastIndex = count($parts)-1;
        $name = str_replace(' ('.$parts[$lastIndex],'',$name);
        return $name;
    }
}
