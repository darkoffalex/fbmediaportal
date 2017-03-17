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
}
