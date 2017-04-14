<?php

namespace app\controllers;

use app\helpers\Constants;
use app\helpers\Help;
use app\models\Category;
use app\models\Comment;
use app\models\Post;
use app\models\User;
use app\models\UserTimeLine;
use Codeception\Util\Debug;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Facebook;
use Yii;
use app\components\Controller;
use yii\base\Exception;
use yii\caching\DbDependency;
use yii\caching\Dependency;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\PageCache;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use Zelenin\Feed;
use Zelenin\yii\extensions\Rss\RssView;

class MainController extends Controller
{
    /**
     * Entry point
     */
    public function actionIndex()
    {
        return $this->actionCategory();
    }

    /**
     * Default actions
     * @return array
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Behaviors for caching
     * @return array
     */
    public function behaviors()
    {
        return [
            //caching for categories
            [
                'class' => PageCache::className(),
                'only' => [
                    'index',
                    'category',
                    'categoryAjax'
                ],
                'duration' => 1800,
                'variations' => [
                    Yii::$app->request->get('id'),
                    Yii::$app->request->get('page',1),
                    Yii::$app->request->get('carousel',0),
                ],
//                'dependency' => [
//                    'class' => DbDependency::className(),
//                    'sql' => 'SELECT MAX(updated_at) FROM post'
//                ],
            ],
            //caching for posts
            [
                'class' => PageCache::className(),
                'only' => [
                    'post',
                ],
                'duration' => 86400,
                'variations' => [
                    Yii::$app->request->get('id'),
                ],
            ],
            //caching for comments
//            [
//                'class' => PageCache::className(),
//                'only' => [
//                    'commentsAjax',
//                    'childrenCommentsAjax'
//                ],
//                'variations' => [
//                    Yii::$app->request->get('id'),
//                    Yii::$app->request->get('page',1),
//                ],
//            ],
            //caching for page 'all'
            [
                'class' => PageCache::className(),
                'only' => [
                    'all',
                ],
                'duration' => 1800,
                'variations' => [
                    Yii::$app->request->get('type'),
                    Yii::$app->request->get('page',1),
                    Yii::$app->request->get('id'),
                ],
            ],
            //caching for page 'pages'
            [
                'class' => PageCache::className(),
                'only' => [
                    'pages',
                ],
                'duration' => 86400,
                'variations' => [
                    Yii::$app->request->get('type'),
                ],
            ]
        ];
    }

    /********************************************** C A T E G O R Y ***************************************************/

    /**
     * Render category page
     * @param null $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionCategory($id = null)
    {
        //use cache for this action
        $cache = false;
        //current category (can be empty, then show all items)
        /* @var $category Category */
        $category = null;
        //category ids which should be used for finding posts (empty for searching whole posts)
        $currentIds = [];
        //siblings categories (second priority for ordering)
        $siblingIds = [];

        //if ID set - try to get category
        if(!empty($id)){
            $catQuery = Category::find()
                ->where(['id' => $id, 'status_id' => Constants::STATUS_ENABLED])
                ->with([
                    'trl',
                    'parent.trl',
                    'parent.childrenActive.childrenActive',
                    'childrenActive.childrenActive'
                ]);

            $category = Help::cquery(function($db)use($catQuery){return $catQuery->one();},$cache);

            if(empty($category)){
                throw new NotFoundHttpException('Рубрика не найдена', 404);
            }
        }

        //set meta data
        $this->view->title = !empty($category) ? $category->trl->name .' - '.(!empty($category->parent) ? $category->parent->trl->name.' - '.$this->view->title : $this->view->title) : "RusTurkey.com – крупнейший русскоязычный портал о Турции";
        $this->view->registerMetaTag(['name' => 'description', 'content' => $this->commonSettings->meta_description]);
        $this->view->registerMetaTag(['name' => 'keywords', 'content' => $this->commonSettings->meta_keywords]);

        //get all category ids which should be included in search query
        if(!empty($category)){

            /* @var $children Category[] */
            $children = $category->getChildrenRecursive(true);
            $currentIds = array_values(ArrayHelper::map($children,'id','id'));
            $currentIds[] = $category->id;

            /* @var $siblings Category[] */
            $siblings = !empty($category->parent) ? $category->parent->getChildrenRecursive(true) : [];
            $siblingIds = !empty($siblings) ? array_values(ArrayHelper::map($siblings,'id','id')) : [];
            foreach ($siblingIds as $index => $id){
                if(in_array($id,$currentIds)){
                    unset($siblingIds[$index]);
                }
            }
        }

        $carouselPostQuery = Post::findSortedEx(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->with(['trl', 'postImages.trl', 'author'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('delayed_at <= NOW()'))
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]))
            ->distinct();

        $mainPostsQuery = Post::findSortedEx(!empty($category) ? $id : null,$currentIds,null)
            ->with(['trl', 'postImages.trl', 'author'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('delayed_at <= NOW()'))
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]))
            ->distinct();

        $lastPostsQuery = Post::find()
            ->with(['trl', 'postImages.trl'])
            ->where(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('delayed_at <= NOW()'))
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]))
            ->orderBy('delayed_at DESC');

        $forumPostsQuery = Post::findSortedEx(!empty($category) ? $id : null,$currentIds,null)
            ->with(['trl', 'postImages.trl'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('delayed_at <= NOW()'))
            ->andWhere(['kind_id' => Constants::KIND_FORUM])
            ->distinct();

        $popularPostsQuery = Post::findSortedPopular(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->with(['trl'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('delayed_at <= NOW()'))
            ->andWhere(new Expression('comment_count > :minCount',['minCount' => 200]))
//            ->andWhere(new Expression('published_at > :minDate', ['minDate' => date('Y-m-d',(time()-(86400*14)))]))
            ->distinct();

        $turkeyPostsQuery = Post::findSortedAboutTurkey(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->distinct()
            ->with(['trl']);

        $mainPostsQuery->limit(10);
        $carouselPostQuery->limit(20);
        $forumPostsQuery->limit(4);

        $lastPostsQuery->limit(7);
        $popularPostsQuery->limit(7);
        $turkeyPostsQuery->limit(7);

        //get main and forum posts posts for first page (next pages will be loaded via ajax)
        $mainPosts = Help::cquery(function($db)use($mainPostsQuery){return $mainPostsQuery->all();},$cache);
        $carouselPosts = Help::cquery(function($db)use($carouselPostQuery){return $carouselPostQuery->all();},$cache);
        $forumPosts = Help::cquery(function($db)use($forumPostsQuery){return $forumPostsQuery->all();},$cache);
        $popularPosts = Help::cquery(function($db)use($popularPostsQuery){return $popularPostsQuery->all();},$cache);
        $turkeyPosts = Help::cquery(function($db)use($turkeyPostsQuery){return $turkeyPostsQuery->all();},$cache);
        $lastPosts = Help::cquery(function($db)use($lastPostsQuery){return $lastPostsQuery->all();},$cache);

//        Help::debug($forumPosts);
//        exit();

        /* @var $mainPosts Post[] */

        //open-graph meta tags
        if(!empty($mainPosts)){
            $this->view->registerMetaTag(['property' => 'og:description', 'content' => $this->commonSettings->meta_description]);
            $this->view->registerMetaTag(['property' => 'og:url', 'content' => Help::canonical()]);
            $this->view->registerMetaTag(['property' => 'og:site_name', 'content' => "RusTurkey.com"]);
            $this->view->registerMetaTag(['property' => 'og:title', 'content' => $this->view->title]);
            $this->view->registerMetaTag(['property' => 'og:image', 'content' => $mainPosts[0]->getFirstImageUrlEx(706,311,true,true,true)]);
            $this->view->registerMetaTag(['property' => 'og:image:width', 'content' => '706']);
            $this->view->registerMetaTag(['property' => 'og:image:height', 'content' => '311']);
        }

        //rendering page
        return $this->render('category',compact('mainPosts','forumPosts','popularPosts','turkeyPosts','lastPosts','carouselPosts','category'));
    }

    /**
     * Ajax loadable content (while scrolling down, or while switching carousel slides)
     * @param null $id
     * @param int $page
     * @param int $carousel
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionCategoryAjax($id = null, $page = 1, $carousel = 0)
    {
        //use cache for this action
        $cache = false;
        //current category (can be empty, then show all items)
        /* @var $category Category */
        $category = null;
        //category ids which should be used for finding posts (empty for searching whole posts)
        $currentIds = [];
        //siblings categories (second priority for ordering)
        $siblingIds = [];

        //if ID set - try to get category
        if(!empty($id)){
            $catQuery = Category::find()
                ->where(['id' => $id, 'status_id' => Constants::STATUS_ENABLED])
                ->with([
                    'trl',
                    'parent.childrenActive.childrenActive',
                    'childrenActive.childrenActive'
                ]);

            $category = Help::cquery(function($db)use($catQuery){return $catQuery->one();},$cache);

            if(empty($category)){
                throw new NotFoundHttpException('Рубрика не найдена', 404);
            }
        }

        //get all category ids which should be included in search query
        if(!empty($category)){
            /* @var $children Category[] */
            $children = $category->getChildrenRecursive(true);
            $currentIds = array_values(ArrayHelper::map($children,'id','id'));
            $currentIds[] = $category->id;

            /* @var $siblings Category[] */
            $siblings = !empty($category->parent) ? $category->parent->getChildrenRecursive(true) : [];
            $siblingIds = !empty($siblings) ? array_values(ArrayHelper::map($siblings,'id','id')) : [];
            foreach ($siblingIds as $index => $id){
                if(in_array($id,$currentIds)){
                    unset($siblingIds[$index]);
                }
            }
        }

        //getting main posts paginated
        $mainPostsQuery = Post::findSortedEx(!empty($category) ? $id : null,$currentIds,$carousel ? $siblingIds : null)
            ->with(['trl', 'postImages.trl', 'author'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('delayed_at <= NOW()'))
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]))
            ->distinct();


        $mainPostsQueryCount = clone $mainPostsQuery;
        $mainPostsCount = Help::cquery(function($db)use($mainPostsQueryCount){return $mainPostsQueryCount->count();},$cache);
        $pagesMain = new Pagination(['totalCount' => $mainPostsCount, 'defaultPageSize' => ($carousel ? 3 : 3)]);
        $mainPosts = Help::cquery(function($db)use($mainPostsQuery,$pagesMain,$carousel){return $mainPostsQuery->offset($pagesMain->offset + ($carousel ? 20 : 5))->limit($pagesMain->limit)->all();},$cache);

        //getting forum posts paginated
        if(!$carousel){
            $forumPostsQuery = Post::findSortedEx(!empty($category) ? $id : null,$currentIds,null)
                ->with(['trl', 'postImages.trl'])
                ->andWhere(['status_id' => Constants::STATUS_ENABLED])
                ->andWhere(new Expression('delayed_at <= NOW()'))
                ->andWhere(['kind_id' => Constants::KIND_FORUM])
                ->distinct();

            $forumPostsQueryCount = clone $forumPostsQuery;
            $forumPostsCount = Help::cquery(function($db)use($forumPostsQueryCount){return $forumPostsQueryCount->count();},$cache);
            $pagesForum = new Pagination(['totalCount' => $forumPostsCount, 'defaultPageSize' => 4]);
            $forumPosts = Help::cquery(function($db)use($forumPostsQuery,$pagesForum){return $forumPostsQuery->offset($pagesForum->offset)->limit($pagesForum->limit)->all();},$cache);

            //load nothing if can't find any data
            if($page > $pagesForum->pageCount && $page > $pagesMain->pageCount){
                return false;
            }elseif($page > $pagesForum->pageCount){
                $forumPosts = [];
            }
        }

        if($carousel){
            return $this->renderPartial('_load_carousel',['posts' => $mainPosts]);
        }

        return $this->renderPartial('_load_category',compact('mainPosts','forumPosts'));
    }


    /************************************************** P O S T *******************************************************/

    /**
     * Preview method
     * @param $id
     * @return string
     */
    public function actionPostPreview($id)
    {
        return $this->actionPost($id,true);
    }

    /**
     * Rendering single post
     * @param $id
     * @param int $preview
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPost($id, $preview = 0)
    {
        //use cache for this action
        $cache = false;
        /* @var $user User */
        $user = Yii::$app->user->identity;

        //getting post
        $postQuery = Post::find()->with([
            'trl',
            'categories.trl',
            'categories.parent.trl',
            'categories.parent.parent.trl',
            'author',
            'postImages',
            'categories.parent.childrenActive.childrenActive',
            'categories.childrenActive.childrenActive',
        ])->where(['id' => $id]);

        if(!$preview){
            $postQuery
                ->andWhere(['status_id' => Constants::STATUS_ENABLED])
                ->andWhere(new Expression('delayed_at <= NOW()'));
        }

        /* @var $post Post */
        $post = Help::cquery(function($db)use($postQuery){return $postQuery->one();},$cache);

        if(empty($post)){
            throw  new NotFoundHttpException('Страница не найдена',404);
        }

        //set meta data
        $description = !empty($post->trl->name) ? $post->trl->small_text : $this->commonSettings->meta_description;
        $keywords = $this->commonSettings->meta_keywords;

        $this->view->title = !empty($post) ? $post->trl->name.' - '.$this->view->title : $this->view->title;
        $this->view->registerMetaTag(['name' => 'description', 'content' => $description]);
        $this->view->registerMetaTag(['name' => 'keywords', 'content' => $keywords]);

        //current category (can be empty, then show all items)
        /* @var $category Category */
        $category = !empty($post->categories[0]) ? $post->categories[0] : null;
        //category ids which should be used for finding posts (empty for searching whole posts)
        $currentIds = [];
        //siblings categories (second priority for ordering)
        $siblingIds = [];

        //get all category ids which should be included in search query (used for carousel only)
        if(!empty($category)){
            /* @var $children Category[] */
            $children = $category->getChildrenRecursive(true);
            $currentIds = array_values(ArrayHelper::map($children,'id','id'));
            $currentIds[] = $category->id;

            /* @var $siblings Category[] */
            $siblings = !empty($category->parent) ? $category->parent->getChildrenRecursive(true) : [];
            $siblingIds = !empty($siblings) ? array_values(ArrayHelper::map($siblings,'id','id')) : [];
            foreach ($siblingIds as $index => $id){
                if(in_array($id,$currentIds)){
                    unset($siblingIds[$index]);
                }
            }
        }

        //get all related with current category posts (to populate carousel)
        $carouselPostsQuery = Post::findSortedEx(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->with(['trl', 'postImages.trl', 'author'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('delayed_at <= NOW()'))
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]))
            ->distinct()
            ->limit(15);

        $carouselPosts = Help::cquery(function($db)use($carouselPostsQuery){return $carouselPostsQuery->all();},$cache);

        //if need - create new comment
        $newComment = $this->addCommentIfNeeded($post,$user);

        //open graph tags
        $this->view->registerMetaTag(['property' => 'og:description', 'content' => $description]);
        $this->view->registerMetaTag(['property' => 'og:url', 'content' => Help::canonical()]);
        $this->view->registerMetaTag(['property' => 'og:site_name', 'content' => "RusTurkey.com"]);
        $this->view->registerMetaTag(['property' => 'og:title', 'content' => $this->view->title]);
        $this->view->registerMetaTag(['property' => 'og:image', 'content' => $post->getFirstImageUrlEx(706,311,true,true,true)]);
        $this->view->registerMetaTag(['property' => 'og:image:width', 'content' => '706']);
        $this->view->registerMetaTag(['property' => 'og:image:height', 'content' => '311']);

        return $this->render('post',compact('post','carouselPosts','newComment'));
    }

    /**
     * Loading comments via ajax while scrolling down
     * @param $id
     * @param int $page
     * @return null|string
     */
    public function actionCommentsAjax($id,$page = 1)
    {
        $q = Comment::find()
            ->where('answer_to_id IS NULL OR answer_to_id = 0')
            ->andWhere(['post_id' => $id])
            ->with([
                'author',
                'children'
            ])
            ->orderBy('created_at ASC');

        /* @var $post Post */
        $post = Post::find()->where(['id' => (int)$id])->one();

        $cq = clone $q;
        $count = Help::cquery(function($db)use($cq){return $cq->count();},false);
        $pages = new Pagination(['totalCount' => $count, 'defaultPageSize' => 10]);

        if($page > $pages->pageCount){
            return null;
        }

        $comments = Help::cquery(function($db)use($q,$pages){return $q->offset($pages->offset)->limit($pages->limit)->all();},false);

        return $this->renderPartial('_load_comments', compact('comments','post'));
    }

    /**
     * Loading children comments via ajax
     * @param $id
     * @return string
     */
    public function actionChildrenCommentsAjax($id)
    {
        $q = Comment::find()
            ->where('answer_to_id = :id', ['id' => $id])
            ->with([
                'author',
                'children',
            ])
            ->orderBy('created_at ASC');

        $comments = Help::cquery(function($db)use($q){return $q->all();},false);

        return $this->renderPartial('_load_comments_child', compact('comments'));
    }

    /**
     * Leaving children comment for post
     * @param null $id
     * @return array|string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionChildrenCommentsAdd($id)
    {
        if(Yii::$app->user->isGuest){
            return $this->renderPartial('_please_login');
        }

        /* @var $post Post */
        /* @var $comment Comment */
        /* @var $user User */
        $comment = Comment::find()->where(['id' => $id])->one();
        $user = Yii::$app->user->identity;
        $post = $comment->post;

        if(empty($post)){
            throw new NotFoundHttpException('Страница не найдена', 404);
        }

        $model = new Comment();
        $model->isFrontend = true;

        if(Yii::$app->request->isPost && $model->load(Yii::$app->request->post())){
            $model->post_id = $post->id;
            $model->author_id = Yii::$app->user->id;
            if(!empty($comment)) $model->answer_to_id = $comment->id;

            if($model->validate()){
                $model->created_by_id = Yii::$app->user->id;
                $model->updated_by_id = Yii::$app->user->id;
                $model->created_at = date('Y-m-d h:i:s',time());
                $model->updated_at = date('Y-m-d h:i:s',time());

                if($model->save()){
                    $post->updated_at = date('Y-m-d h:i:s',time());
                    $post->updated_by_id = Yii::$app->user->id;
                    $post->update();

                    $user->refresh();
                    $user->counter_comments = count($user->comments);
                    $user->update();

                    $tli = new UserTimeLine();
                    $tli->published_at = $model->created_at;
                    $tli->comment_id = $model->id;
                    $tli->user_id = $user->id;
                    $tli->save();

                    if(!empty($user->fb_user_id) && !empty($comment->fb_sync_id)){
                        $name = $user->name.' '.$user->surname;
                        $message = "Пользователем {$name} был добавлен комментарий: «{$model->text}»";
                        $fbId = Help::fbcomment($comment->fb_sync_id,$message);

                        if(!empty($fbId)){
                            $model->fb_sync_id = $fbId;
                            $model->update();
                        }
                    }
                }
            }
        }

        return $this->actionChildrenCommentsAjax($id);
    }

    /**
     * Adding comment using data from POST request
     * @param $post Post
     * @param $user User
     * @return Comment
     */
    public function addCommentIfNeeded($post,$user)
    {
        //C O M M E N T  A D D I N G
        $newComment = new Comment();
        $newComment->isFrontend = true;

        if(empty($user) || Yii::$app->user->isGuest){
            return $newComment;
        }

        if(Yii::$app->request->isPost && $newComment->load(Yii::$app->request->post())){
            $newComment->post_id = $post->id;
            $newComment->author_id = Yii::$app->user->id;
            if(!empty($newComment)) $newComment->answer_to_id = $newComment->id;

            if($newComment->validate()){
                $newComment->created_by_id = Yii::$app->user->id;
                $newComment->updated_by_id = Yii::$app->user->id;
                $newComment->created_at = date('Y-m-d h:i:s',time());
                $newComment->updated_at = date('Y-m-d h:i:s',time());

                if($newComment->save()){
                    $post->refresh();
                    $post->updated_at = date('Y-m-d h:i:s',time());
                    $post->updated_by_id = Yii::$app->user->id;
                    $post->comment_count = count($post->comments);
                    $post->update();

                    $user->refresh();
                    $user->counter_comments = count($user->comments);
                    $user->update();

                    $tli = new UserTimeLine();
                    $tli->published_at = $newComment->created_at;
                    $tli->comment_id = $newComment->id;
                    $tli->user_id = $user->id;
                    $tli->save();

                    if(!empty($user->fb_user_id) && !empty($post->fb_sync_id)){
                        $name = $user->name.' '.$user->surname;
                        $message = "Пользователем {$name} был добавлен комментарий: «{$newComment->text}»";
                        $fbId = Help::fbcomment($post->fb_sync_id,$message);

                        if(!empty($fbId)){
                            $newComment->fb_sync_id = $fbId;
                            $newComment->update();
                        }
                    }
                }
            }
        }

        return $newComment;
    }


    /********************************************* P R O F I L E ******************************************************/

    /**
     * Profile page
     * @param null|int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionProfile($id = null)
    {
        //use cache for this action
        $cache = false;

        /* @var $user User */
        $user = !Yii::$app->user->isGuest ? Yii::$app->user->identity : null;

        if(!empty($id)){
            $user = User::find()->where(['id' => $id])->one();
        }

        if(empty($user)){
            throw new NotFoundHttpException('Пользователь не найден', 404);
        }

        //set meta data
        $this->view->title = $user->name.' '.$user->surname.' - Профиль участника - '.$this->view->title;
        $this->view->registerMetaTag(['name' => 'description', 'content' => $this->commonSettings->meta_description]);
        $this->view->registerMetaTag(['name' => 'keywords', 'content' => $this->commonSettings->meta_keywords]);

        //query activity list
        $q = UserTimeLine::find()->where(['user_id' => $user->id]);
        $countQ = clone $q;
        $q->with([
            'post.trl',
            'comment.post.trl',
            'comment.author',
            'post.postImages.trl',
            'post.author'
        ])->orderBy('published_at DESC');

        $count = Help::cquery(function($db)use($countQ){return $countQ->count();},$cache);
        $pages = new Pagination(['totalCount' => $count, 'defaultPageSize' => 20]);
        $items = Help::cquery(function($db)use($q, $pages){return $q->limit($pages->limit)->offset($pages->offset)->all();},$cache);

        //posts for carousel
        $carouselPostsQuery = Post::findSortedEx()
            ->with(['trl', 'postImages.trl'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('delayed_at <= NOW()'))
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]))
            ->distinct()
            ->limit(15);

        $carouselPosts = Help::cquery(function($db)use($carouselPostsQuery){return $carouselPostsQuery->all();},$cache);

        //counters
        $materialCount = Post::find()->where(['author_id' => $user->id, 'status_id' => Constants::STATUS_ENABLED])->andWhere(new Expression('delayed_at <= NOW()'))->andWhere('content_type_id != :type',['type' => Constants::CONTENT_TYPE_POST])->count();
        $postCount = Post::find()->where(['author_id' => $user->id, 'status_id' => Constants::STATUS_ENABLED, 'content_type_id' => Constants::CONTENT_TYPE_POST])->andWhere(new Expression('delayed_at <= NOW()'))->count();
        $commentCount = Comment::find()->alias('c')->joinWith('post as p')->where('c.author_id = :author AND p.status_id = :status',['author' => $user->id, 'status' => Constants::STATUS_ENABLED])->count();

        return $this->render('profile', compact('items','pages','user','carouselPosts','materialCount','postCount','commentCount'));
    }

    /**
     * Profile detailed information
     * @param null $id
     * @param string $type
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionProfileDetails($id = null, $type = 'posts')
    {
        //use cache for this action
        $cache = false;

        /* @var $user User */
        $user = !Yii::$app->user->isGuest ? Yii::$app->user->identity : null;

        if(!empty($id)){
            $user = User::find()->where(['id' => $id])->one();
        }

        if(empty($user)){
            throw new NotFoundHttpException('Пользователь не найден', 404);
        }

        //set meta data
        $this->view->title = $user->name.' '.$user->surname.' - Записи участника - '.$this->view->title;
        $this->view->registerMetaTag(['name' => 'description', 'content' => '']);
        $this->view->registerMetaTag(['name' => 'keywords', 'content' => '']);

        $posts = [];
        $comments = [];

        switch ($type){
            case 'posts':
                $q = Post::find()->where([
                    'author_id' => $user->id,
                    'status_id' => Constants::STATUS_ENABLED,
                    'content_type_id' => Constants::CONTENT_TYPE_POST])
                    ->andWhere(new Expression('delayed_at <= NOW()'))
                    ->orderBy('delayed_at DESC');
                $qc = clone $q;
                break;
            case 'materials':
                $q = Post::find()
                    ->where(['author_id' => $user->id, 'status_id' => Constants::STATUS_ENABLED])
                    ->andWhere(new Expression('delayed_at <= NOW()'))
                    ->andWhere('content_type_id != :type',['type' => Constants::CONTENT_TYPE_POST])
                    ->orderBy('published_at DESC');
                $qc = clone $q;
                break;
            case 'comments':
                $q = Comment::find()
                    ->with(['post.trl','author'])
                    ->alias('c')
                    ->joinWith('post as p')
                    ->where('c.author_id = :author AND p.status_id = :status',['author' => $user->id, 'status' => Constants::STATUS_ENABLED])
                    ->orderBy('created_at DESC')
                    ->distinct();
                $qc = clone $q;
                break;
            default :
                $q = Post::find()
                    ->where(['author_id' => $user->id, 'status_id' => Constants::STATUS_ENABLED])
                    ->andWhere(new Expression('delayed_at <= NOW()'))
                    ->andWhere('content_type_id != :type',['type' => Constants::CONTENT_TYPE_POST])
                    ->orderBy('published_at DESC');
                $qc = clone $q;
                break;
        }

        $count = $count = Help::cquery(function($db)use($qc){return $qc->count();},$cache);
        $pages = new Pagination(['totalCount' => $count, 'defaultPageSize' => 20]);

        if($type == 'comments'){
            $q->with(['children','author'])
                ->offset($pages->offset)
                ->limit($pages->limit);
            $comments = Help::cquery(function($db)use($q){return $q->all();},$cache);
        }else{
            $q->with(['trl','postImages','author'])
                ->offset($pages->offset)
                ->limit($pages->limit);
            $posts = Help::cquery(function($db)use($q){return $q->all();},$cache);
        }

        //posts for carousel
        $carouselPostsQuery = Post::findSortedEx()
            ->with(['trl', 'postImages.trl'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('delayed_at <= NOW()'))
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]))
            ->distinct()
            ->limit(15);

        $carouselPosts = Help::cquery(function($db)use($carouselPostsQuery){return $carouselPostsQuery->all();},$cache);

        return $this->render('profile_details',compact('posts','comments','carouselPosts','pages','user','type'));
    }

    /************************************************* A L L  *********************************************************/

    /**
     * Extended list of posts
     * @param string $type
     * @param null|int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAll($type, $id = null)
    {
        //use cache for this action
        $cache = false;
        //current category (can be empty, then show all items)
        /* @var $category Category */
        $category = null;
        //category ids which should be used for finding posts (empty for searching whole posts)
        $currentIds = [];
        //siblings categories (second priority for ordering)
        $siblingIds = [];

        //if ID set - try to get category
        if(!empty($id)){
            $catQuery = Category::find()
                ->where(['id' => $id, 'status_id' => Constants::STATUS_ENABLED])
                ->andWhere(new Expression('delayed_at <= NOW()'))
                ->with([
                    'trl',
                    'parent.childrenActive.childrenActive',
                    'childrenActive.childrenActive'
                ]);

            $category = Help::cquery(function($db)use($catQuery){return $catQuery->one();},false);

            if(empty($category)){
                throw new NotFoundHttpException('Рубрика не найдена', 404);
            }
        }

        $titles = [
            'latest' => 'Последнее',
            'popular' => 'Популярное',
            'turkey' => 'Полезное о Турции'
        ];

        $this->view->title = ArrayHelper::getValue($titles,$type,'Последние').' - '.$this->view->title;
        $this->view->registerMetaTag(['name' => 'description', 'content' => '']);
        $this->view->registerMetaTag(['name' => 'keywords', 'content' => '']);

        //get all category ids which should be included in search query
        if(!empty($category)){

            /* @var $children Category[] */
            $children = $category->getChildrenRecursive(true);
            $currentIds = array_values(ArrayHelper::map($children,'id','id'));
            $currentIds[] = $category->id;

            /* @var $siblings Category[] */
            $siblings = !empty($category->parent) ? $category->parent->getChildrenRecursive(true) : [];
            $siblingIds = !empty($siblings) ? array_values(ArrayHelper::map($siblings,'id','id')) : [];
            foreach ($siblingIds as $index => $id){
                if(in_array($id,$currentIds)){
                    unset($siblingIds[$index]);
                }
            }
        }

        $mainPostsQuery = Post::find()
            ->with(['trl', 'postImages.trl'])
            ->where(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('delayed_at <= NOW()'))
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]))
            ->orderBy('published_at DESC');


        $popularPostsQuery = Post::findSortedPopular(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->with(['trl', 'postImages.trl', 'author'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('delayed_at <= NOW()'))
            ->andWhere(new Expression('comment_count > :minCount',['minCount' => 200]))
            ->andWhere(new Expression('published_at > :minDate', ['minDate' => date('Y-m-d H:i:s',(time()-(86400*14)))]))
            ->distinct();

        $turkeyPostsQuery = Post::findSortedAboutTurkey(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->distinct()
            ->with(['trl']);

        switch ($type){
            case 'latest':
                $q = $mainPostsQuery;
                break;
            case 'popular':
                $q = $popularPostsQuery;
                break;
            case 'turkey':
                $q = $turkeyPostsQuery;
                break;
            default:
                $q = $mainPostsQuery;
                break;
        }

        $qc = clone $q;

        //get and paginate (15 per page)
        $count = $count = Help::cquery(function($db)use($qc){return $qc->count();},$cache);
        $pages = new Pagination(['totalCount' => $count, 'defaultPageSize' => 15]);
        $q->offset($pages->offset)->limit($pages->limit);
        $posts = Help::cquery(function($db)use($q){return $q->all();},$cache);

        $carouselPosts = Help::cquery(function($db)use($mainPostsQuery){return $mainPostsQuery->limit(15)->all();},$cache);

        return $this->render('all',compact('category','posts','pages','type','carouselPosts'));
    }

    /*********************************************** S E A R C H  *****************************************************/

    /**
     * Performs search by keywords
     * @return string
     */
    public function actionSearch()
    {
        //use cache for this action
        $cache = false;

        $query = Yii::$app->request->get('q');

        $q = Post::find()
            ->alias('p')
            ->andWhere(['p.status_id' => Constants::STATUS_ENABLED]);

        if(!empty($query)){
            $words = explode(' ',$query);
            foreach($words as $index => $word){
                $q->andWhere(['like','p.search_keywords',$word]);
            }
        }else{
            $q->andWhere(['id' => 0]);
        }

        $q->orderBy('p.published_at DESC');

        $cq = clone $q;

        /* @var $pages Pagination */
        $pages = new Pagination(['totalCount' => $cq->count(), 'defaultPageSize' => 20]);

        if(empty($query)){
            $posts = $q->all();
        }else{
            $posts = $q->with(['trl', 'postImages','author'])
                ->offset($pages->offset)
                ->limit($pages->limit)
                ->all();
        }

        //set meta data
        $this->view->title = $query.' - '.$this->view->title;
        $this->view->registerMetaTag(['name' => 'description', 'content' => $this->commonSettings->meta_description]);
        $this->view->registerMetaTag(['name' => 'keywords', 'content' => $this->commonSettings->meta_keywords]);

        //posts for carousel
        $carouselPostsQuery = Post::findSortedEx()
            ->with(['trl', 'postImages.trl'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('delayed_at <= NOW()'))
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]))
            ->distinct()
            ->limit(15);

        $carouselPosts = Help::cquery(function($db)use($carouselPostsQuery){return $carouselPostsQuery->all();},$cache);

        return $this->render('search',compact('posts','carouselPosts','query','pages'));
    }

    /**
     * Static pages
     * @param $type
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPages($type)
    {

        $cache = false;

        //set meta data
        $titles = [
            'about' => 'О проекте',
            'advertising' => 'Реклама',
            'politics' => 'Политика безопасности',
            'agreement' => 'Пользовательское соглашение',
            'widgets' => 'Полезные виджеты'
        ];

        $this->view->title = ArrayHelper::getValue($titles,$type,'Безымянная страница').' - '.$this->view->title;
        $this->view->registerMetaTag(['name' => 'description', 'content' => $this->commonSettings->meta_description]);
        $this->view->registerMetaTag(['name' => 'keywords', 'content' => $this->commonSettings->meta_keywords]);

        //posts for carousel
        $carouselPostsQuery = Post::findSortedEx()
            ->with(['trl', 'postImages.trl'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('delayed_at <= NOW()'))
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]))
            ->distinct()
            ->limit(15);

        $carouselPosts = Help::cquery(function($db)use($carouselPostsQuery){return $carouselPostsQuery->all();},$cache);

        try{
            $title = ArrayHelper::getValue($titles,$type,'Безымянная страница');
            $rendered = $this->render('static_'.$type, compact('carouselPosts', 'title'));
        }catch (\Exception $ex){
            throw new NotFoundHttpException('Страница не найдена',404);
        }

        return $rendered;
    }

    /**
     * Rss
     */
    public function actionRss()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Post::findSortedEx(null,null,null,false)
                ->with(['trl', 'postImages.trl', 'author'])
                ->andWhere(new Expression('delayed_at <= NOW()'))
                ->distinct(),
            'pagination' => [
                'pageSize' => 100
            ],
        ]);

        $response = Yii::$app->getResponse();
        $headers = $response->getHeaders();

        $headers->set('Content-Type', 'application/rss+xml; charset=utf-8');

        echo RssView::widget([
            'dataProvider' => $dataProvider,
            'channel' => [
                'title' => function ($widget, Feed $feed) {
                    $feed->addChannelTitle(Yii::$app->name);
                },
                'link' => Url::toRoute('/', true),
                'description' => 'Все материалы',
                'language' => function ($widget, Feed $feed) {
                    return Yii::$app->language;
                },
//                'image'=> function ($widget, Feed $feed) {
//                    $feed->addChannelImage('http://example.com/channel.jpg', 'http://example.com', 88, 31, 'Image description');
//                },
            ],
            'items' => [
                'title' => function ($model, $widget, Feed $feed) {
                    /* @var $model Post */
                    return $model->trl->name;
                },
                'description' => function ($model, $widget, Feed $feed) {
                    /* @var $model Post */
                    return $model->trl->small_text;
                },
                'link' => function ($model, $widget, Feed $feed) {
                    /* @var $model Post */
                    return $model->getUrl();
                },
                'author' => function ($model, $widget, Feed $feed) {
                    /* @var $model Post */
                    return $model->author->name.' '.$model->author->surname;
                },
//                'guid' => function ($model, $widget, Feed $feed) {
//                    /* @var $model Post */
//                    $date = \DateTime::createFromFormat('Y-m-d H:i:s', $model->updated_at);
//                    return Url::toRoute(['post/view', 'id' => $model->id], true) . ' ' . $date->format(DATE_RSS);
//                },
                'pubDate' => function ($model, $widget, Feed $feed) {
                    /* @var $model Post */
                    $date = \DateTime::createFromFormat('Y-m-d H:i:s', $model->delayed_at);
                    return $date->format(DATE_RSS);
                }
            ]
        ]);
    }
}