<?php

namespace app\controllers;

use app\helpers\Constants;
use app\helpers\Help;
use app\models\Category;
use app\models\Comment;
use app\models\Post;
use app\models\User;
use Yii;
use app\components\Controller;
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

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
        $cache = true;
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

            //set meta data
            $this->view->title = !empty($category) ? $category->trl->name : $this->view->title;
            $this->view->registerMetaTag(['name' => 'description', 'content' => !empty($category->trl->meta_description) ? $category->trl->meta_description : $this->commonSettings->meta_description]);
            $this->view->registerMetaTag(['name' => 'keywords', 'content' => !empty($category->trl->meta_keywords) ? $category->trl->meta_keywords : $this->commonSettings->meta_keywords]);

            //open-graph meta tags
//            $this->view->registerMetaTag(['property' => 'og:description', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:url', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:site_name', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:title', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:image', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:image:width', 'content' => '200']);
//            $this->view->registerMetaTag(['property' => 'og:image:height', 'content' => '200']);

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

        $mainPostsQuery = Post::findSorted(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->with(['trl', 'postImages.trl', 'author'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]));

        $forumPostsQuery = Post::findSorted(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->with(['trl', 'postImages.trl'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED]);
//            ->andWhere(['kind_id' => Constants::KIND_FORUM]);

        $popularPostsQuery = Post::findSortedPopular(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->with(['trl'])
            ->andWhere(new Expression('comment_count > :minCount',['minCount' => 200]))
            ->andWhere(new Expression('published_at > :minDate', ['minDate' => date('Y-m-d H:i:s',(time()-(86400*100)))]));

        $mainPostsQuery->limit(15);
        $forumPostsQuery->limit(4);
        $popularPostsQuery->limit(7);

        //get main and forum posts posts for first page (next pages will be loaded via ajax)
        $mainPosts = Help::cquery(function($db)use($mainPostsQuery){return $mainPostsQuery->all();},$cache);
        $forumPosts = Help::cquery(function($db)use($forumPostsQuery){return $forumPostsQuery->all();},$cache);
        $popularPosts = Help::cquery(function($db)use($popularPostsQuery){return $popularPostsQuery->all();},$cache);

        //rendering page
        return $this->render('category',compact('mainPosts','forumPosts','popularPosts','category'));
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
        $cache = true;
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
        $mainPostsQuery = Post::findSorted(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->with(['trl', 'postImages.trl', 'author'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]));

        $mainPostsQueryCount = clone $mainPostsQuery;
        $mainPostsCount = Help::cquery(function($db)use($mainPostsQueryCount){return $mainPostsQueryCount->count();},$cache);
        $pagesMain = new Pagination(['totalCount' => $mainPostsCount, 'defaultPageSize' => ($carousel ? 1 : 3)]);
        $mainPosts = Help::cquery(function($db)use($mainPostsQuery,$pagesMain,$carousel){return $mainPostsQuery->offset($pagesMain->offset + ($carousel ? 15 : 5))->limit($pagesMain->limit)->all();},$cache);

        //getting forum posts paginated
        if(!$carousel){
            $forumPostsQuery = Post::findSorted(!empty($category) ? $id : null,$currentIds,$siblingIds)
                ->with(['trl', 'postImages.trl', 'author'])
                ->andWhere(['status_id' => Constants::STATUS_ENABLED]);
//            ->andWhere(['kind_id' => Constants::KIND_FORUM]);

            $forumPostsQueryCount = clone $forumPostsQuery;
            $forumPostsCount = Help::cquery(function($db)use($forumPostsQueryCount){return $forumPostsQueryCount->count();},$cache);
            $pagesForum = new Pagination(['totalCount' => $forumPostsCount, 'defaultPageSize' => 4]);
            $forumPosts = Help::cquery(function($db)use($forumPostsQuery,$pagesForum){return $forumPostsQuery->offset($pagesForum->offset)->limit($pagesForum->limit)->all();},$cache);
        }

        if($carousel){
            return $this->renderPartial('_load_carousel',['posts' => $mainPosts]);
        }

        return $this->renderPartial('_load_category',compact('mainPosts','forumPosts'));
    }


    /************************************************** P O S T *******************************************************/

    /**
     * Rendering single post
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPost($id)
    {
        //use cache for this action
        $cache = true;
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
        ])->where(['id' => $id, 'status_id' => Constants::STATUS_ENABLED]);

        /* @var $post Post */
        $post = Help::cquery(function($db)use($postQuery){return $postQuery->one();},$cache);

        if(empty($post)){
            throw  new NotFoundHttpException('Страница не найдена',404);
        }

        //set meta data
        $this->view->title = !empty($category) ? $post->trl->name : $this->view->title;
        $this->view->registerMetaTag(['name' => 'description', 'content' => !empty($post->trl->name) ? $post->trl->name : $this->commonSettings->meta_description]);
        $this->view->registerMetaTag(['name' => 'keywords', 'content' => !empty($post->trl->name) ? $post->trl->name : $this->commonSettings->meta_keywords]);

        //open-graph meta tags
//            $this->view->registerMetaTag(['property' => 'og:description', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:url', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:site_name', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:title', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:image', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:image:width', 'content' => '200']);
//            $this->view->registerMetaTag(['property' => 'og:image:height', 'content' => '200']);

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
        $carouselPostsQuery = Post::findSorted(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->with(['trl', 'postImages.trl', 'author'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]))
            ->limit(15);

        $carouselPosts = Help::cquery(function($db)use($carouselPostsQuery){return $carouselPostsQuery->all();},$cache);

        //if need - create new comment
        $newComment = $this->addCommentIfNeeded($post,$user);

        //get comments for current post (first 10)
        $q = Comment::find()
            ->where('answer_to_id IS NULL OR answer_to_id = 0')
            ->andWhere(['post_id' => $post->id])
            ->with([
                'author',
                'children',
            ])
            ->orderBy('created_at ASC');

        $cq = clone $q;
        $count = Help::cquery(function($db)use($cq){return $cq->count();},$cache);
        $pages = new Pagination(['totalCount' => $count, 'defaultPageSize' => 10]);
        $comments = Help::cquery(function($db)use($q,$pages){return $q->offset($pages->offset)->limit($pages->limit)->all();},$cache);

        return $this->render('post',compact('post','comments','carouselPosts','newComment'));
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
                'children',
            ])
            ->orderBy('created_at ASC');

        $cq = clone $q;
        $count = Help::cquery(function($db)use($cq){return $cq->count();},false);
        $pages = new Pagination(['totalCount' => $count, 'defaultPageSize' => 10]);

        if($page > $pages->pageCount){
            return null;
        }

        $comments = Help::cquery(function($db)use($q,$pages){return $q->offset($pages->offset)->limit($pages->limit)->all();},false);

        return $this->renderPartial('_load_comments', compact('comments'));
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

                    if(!empty($user->fb_user_id)){
                        //TODO: Apply changes in FB
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

                    if(!empty($user->fb_user_id)){
                        //TODO: Apply changes in FB
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
        $this->view->title = "Профиль участника ".$user->name.' '.$user->surname;
        $this->view->registerMetaTag(['name' => 'description', 'content' => '']);
        $this->view->registerMetaTag(['name' => 'keywords', 'content' => '']);

        //query activity list
        $sql = "SELECT post_id, comment_id, content, post_name, author_id, published_at, type, image_path, image_url, `user`.avatar_file, `user`.name, `user`.surname FROM (SELECT * FROM (SELECT  post.id as post_id, NULL as comment_id, post_trl.text as content, post_trl.name as post_name, author_id, published_at, 'post' as type, (SELECT file_path FROM post_image WHERE post_image.post_id = post.id ORDER BY priority ASC LIMIT 1) image_path,(SELECT file_url FROM post_image WHERE post_image.post_id = post.id ORDER BY priority ASC LIMIT 1) image_url FROM `post` JOIN post_trl ON post_trl.post_id = post.id AND post_trl.lng = :lng WHERE author_id = :user) p UNION SELECT * FROM (SELECT NULL as post_id, `comment`.id as comment_id, text as content, NULL as post_name, author_id, created_at as published_at, 'comment' as type, NULL as image_path, NULL as image_url FROM `comment` WHERE author_id = :user) c) activity_log JOIN `user` ON activity_log.author_id = `user`.id ORDER BY published_at DESC LIMIT :limit OFFSET :offset";
        $sqlCount = "SELECT COUNT(*) FROM (SELECT * FROM (SELECT  post.id as post_id, NULL as comment_id, post_trl.text as content, author_id, published_at, 'post' as type, (SELECT file_path FROM post_image WHERE post_image.post_id = post.id ORDER BY priority ASC LIMIT 1) image_path,(SELECT file_url FROM post_image WHERE post_image.post_id = post.id ORDER BY priority ASC LIMIT 1) image_url FROM `post` JOIN post_trl ON post_trl.post_id = post.id AND post_trl.lng = :lng WHERE author_id = :user) p UNION SELECT * FROM (SELECT NULL as post_id, `comment`.id as comment_id, text as content, author_id, created_at as published_at, 'comment' as type, NULL as image_path, NULL as image_url FROM `comment` WHERE author_id = :user) c) activity_log JOIN `user` ON activity_log.author_id = `user`.id ORDER BY published_at";

        $count = Yii::$app->db->createCommand($sqlCount,['user' => $user->id, 'lng' => Yii::$app->language])->queryScalar();
        $pages = new Pagination(['totalCount' => $count, 'defaultPageSize' => 20]);
        $items = Yii::$app->db->createCommand($sql,['user' => $user->id,'limit' => $pages->limit, 'offset' => $pages->offset, 'lng' => Yii::$app->language])->queryAll();

        //posts for carousel
        $carouselPostsQuery = Post::findSorted()
            ->with(['trl', 'postImages.trl'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]))
            ->limit(15);

        $carouselPosts = Help::cquery(function($db)use($carouselPostsQuery){return $carouselPostsQuery->all();},$cache);

        //counters
        $materialCount = Post::find()->where(['author_id' => $user->id, 'status_id' => Constants::STATUS_ENABLED])->andWhere('content_type_id != :type',['type' => Constants::CONTENT_TYPE_POST])->count();
        $postCount = Post::find()->where(['author_id' => $user->id, 'status_id' => Constants::STATUS_ENABLED, 'content_type_id' => Constants::CONTENT_TYPE_POST])->count();

        return $this->render('profile', compact('items','pages','user','carouselPosts','materialCount','postCount'));
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
        $this->view->title = "Профиль участника ".$user->name.' '.$user->surname;
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
                    ->orderBy('published_at DESC');
                $qc = clone $q;
                break;
            case 'materials':
                $q = Post::find()
                    ->where(['author_id' => $user->id, 'status_id' => Constants::STATUS_ENABLED])
                    ->andWhere('content_type_id != :type',['type' => Constants::CONTENT_TYPE_POST])
                    ->orderBy('published_at DESC');
                $qc = clone $q;
                break;
            case 'comments':
                $q = Comment::find()
                    ->where(['author_id' => $user->id])
//                    ->andWhere(new Expression('answer_to_id IS NULL OR answer_to_id = 0'))
                    ->orderBy('created_at DESC');
                $qc = clone $q;
                break;
            default :
                $q = Post::find()
                    ->where(['author_id' => $user->id, 'status_id' => Constants::STATUS_ENABLED])
                    ->andWhere('content_type_id != :type',['type' => Constants::CONTENT_TYPE_POST])
                    ->orderBy('published_at DESC');
                $qc = clone $q;
                break;
        }

        $count = $count = Help::cquery(function($db)use($qc){return $qc->count();},false);
        $pages = new Pagination(['totalCount' => $count, 'defaultPageSize' => 20]);

        if($type == 'comments'){
            $q->with(['children','author'])
                ->offset($pages->offset)
                ->limit($pages->limit);
            $comments = Help::cquery(function($db)use($q){return $q->all();},false);
        }else{
            $q->with(['trl','postImages','author'])
                ->offset($pages->offset)
                ->limit($pages->limit);
            $posts = Help::cquery(function($db)use($q){return $q->all();},false);
        }

        //posts for carousel
        $carouselPostsQuery = Post::findSorted()
            ->with(['trl', 'postImages.trl'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]))
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

        //get all category ids which should be included in search query
        if(!empty($category)){

            //set meta data
            $this->view->title = !empty($category) ? $category->trl->name : $this->view->title . ' |  все материалы';
            $this->view->registerMetaTag(['name' => 'description', 'content' => !empty($category->trl->meta_description) ? $category->trl->meta_description : $this->commonSettings->meta_description]);
            $this->view->registerMetaTag(['name' => 'keywords', 'content' => !empty($category->trl->meta_keywords) ? $category->trl->meta_keywords : $this->commonSettings->meta_keywords]);


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

        $mainPostsQuery = Post::findSorted(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->with(['trl', 'postImages.trl', 'author'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]));


        $popularPostsQuery = Post::findSortedPopular(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->with(['trl', 'postImages.trl', 'author'])
            ->andWhere(new Expression('comment_count > :minCount',['minCount' => 200]))
            ->andWhere(new Expression('published_at > :minDate', ['minDate' => date('Y-m-d H:i:s',(time()-(86400*100)))]));

        switch ($type){
            case 'latest':
                $q = $mainPostsQuery;
                break;
            case 'popular':
                $q = $popularPostsQuery;
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

        //if current type is "popular" - use for carousel another query, if not - use current (per page must be 15! (because of carousel ajax loading offset)
        $carouselPosts = $type == 'popular' ? Help::cquery(function($db)use($mainPostsQuery){return $mainPostsQuery->limit(15)->all();},$cache) : $posts;

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
        $this->view->title = 'Поиск';
        $this->view->registerMetaTag(['name' => 'description', 'content' => 'Поиск']);
        $this->view->registerMetaTag(['name' => 'keywords', 'content' => 'Поиск']);

        //posts for carousel
        $carouselPostsQuery = Post::findSorted()
            ->with(['trl', 'postImages.trl'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]))
            ->limit(15);

        $carouselPosts = Help::cquery(function($db)use($carouselPostsQuery){return $carouselPostsQuery->all();},$cache);

        return $this->render('search',compact('posts','carouselPosts','query','pages'));
    }
}