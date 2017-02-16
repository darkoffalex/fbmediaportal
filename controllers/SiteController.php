<?php

namespace app\controllers;

use app\helpers\Constants;
use app\helpers\Help;
use app\models\Banner;
use app\models\Category;
use app\models\Post;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\GraphNodes\GraphPicture;
use Yii;
use app\components\Controller;
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotAcceptableHttpException;
use app\models\User;
use yii\web\NotFoundHttpException;

class SiteController extends Controller
{
    /**
     * @inheritdoc
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
     * Главная страница
     * @return string
     */
    public function actionIndex()
    {
        /* @var $posts Post[] */
        $posts = Post::find()
            ->alias('p')
            ->with(['trl','postImages.trl', 'author', 'comments'])
            ->where(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('kind_id != :kind OR kind_id IS NULL', ['kind' => Constants::KIND_FORUM]))
//            ->andWhere(new Expression('EXISTS (SELECT img.id FROM post_image img WHERE img.post_id = p.id)'))
            ->orderBy(new Expression('IF(sticky_position_main, sticky_position_main, 2147483647) ASC, IF(type_id = :lowestPriorityType, 2147483647, 0) ASC, published_at DESC',
                ['lowestPriorityType' => Constants::CONTENT_TYPE_POST]
            ))
            ->offset(0)
            ->limit(6)
            ->all();

        /* @var $forumPosts Post[] */
        $forumPosts = Post::find()
            ->alias('p')
            ->with(['trl','postImages'])
            ->where(['status_id' => Constants::STATUS_ENABLED])
//            ->andWhere(new Expression('EXISTS (SELECT img.id FROM post_image img WHERE img.post_id = p.id)'))
//            ->andWhere(['kind_id' => Constants::KIND_FORUM])
            ->orderBy('published_at DESC')
            ->offset(0)
            ->limit(4)
            ->all();

        return $this->render('index', compact('posts','forumPosts'));
    }

    /**
     * Outputs all items
     * @param $type
     * @return string
     */
    public function actionAll($type)
    {
        $minComments = 200;
        $minDate = '2014.01.01 00:00:00';
        $q = Post::find();

        switch ($type){
            case 'latest':
                $q->andWhere(new Expression('status_id = :status AND (kind_id != :kind OR kind_id IS NULL)', ['status' => Constants::STATUS_ENABLED, 'kind' => Constants::KIND_FORUM]));
                $q->orderBy('published_at DESC');
                break;
            case 'popular':
                $q->andWhere('last_comment_at > :minDate AND comment_count > :minComments AND status_id = :status',
                    [
                        'minDate' => $minDate,
                        'minComments' => $minComments,
                        'status' => Constants::STATUS_ENABLED
                    ]);
                $q->orderBy('comment_count DESC');
                break;
            default:
                $type = 'latest';
                $q->andWhere(new Expression('status_id = :status AND (kind_id != :kind OR kind_id IS NULL)', ['status' => Constants::STATUS_ENABLED, 'kind' => Constants::KIND_FORUM]));
                break;
        }

        $countQ = clone $q;
        $pages = new Pagination(['totalCount' => $countQ->count(), 'defaultPageSize' => 20]);
        $q->with(['trl','postImages','author']);
        $posts = $q->offset($pages->offset)->limit($pages->limit)->all();

        return $this->render('all', compact('type','posts','pages'));
    }

    /**
     * Post-loading via ajax for carousels
     * @param $page
     * @param null $cat
     * @param null $additional
     * @return string
     */
    public function actionCarouselLoad($page, $cat = null, $additional = null)
    {
        $q = Post::find()
            ->alias('p')
            ->with(['trl','postImages.trl', 'author', 'comments'])
            ->where(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(['content_type_id' => Constants::CONTENT_TYPE_POST])
            ->andWhere(new Expression('kind_id != :kind OR kind_id IS NULL', ['kind' => Constants::KIND_FORUM]))
            ->andWhere(new Expression('EXISTS (SELECT img.id FROM post_image img WHERE img.post_id = p.id)'))
            ->orderBy(new Expression('IF(sticky_position_main, sticky_position_main, 2147483647) ASC, IF(type_id = :lowestPriorityType, 2147483647, 0) ASC, published_at DESC',
                ['lowestPriorityType' => Constants::CONTENT_TYPE_POST]
            ));


        $count = clone $q;
        $pages = new Pagination(['totalCount' => $count->count(), 'defaultPageSize' => 1]);
        $posts = $q->offset($pages->offset+20)->limit($pages->limit)->all();

        return $this->renderPartial('_load_carousel',compact('posts'));
    }

    /**
     * Post-loading via ajax (while scrolling)
     * @param int $page
     * @return null|string
     */
    public function actionPostLoad($page = 1)
    {
        $qMain = Post::find()
            ->alias('p')
            ->with(['trl','postImages.trl', 'author', 'comments'])
            ->where(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('kind_id != :kind OR kind_id IS NULL', ['kind' => Constants::KIND_FORUM]))
//            ->andWhere(new Expression('EXISTS (SELECT img.id FROM post_image img WHERE img.post_id = p.id)'))
            ->orderBy(new Expression('IF(sticky_position_main, sticky_position_main, 2147483647) ASC, IF(content_type_id = :lowestPriorityType, 2147483647, 0) ASC, published_at DESC',
                ['lowestPriorityType' => Constants::CONTENT_TYPE_POST]
            ));

        $qForum = Post::find()
            ->alias('p')
            ->with(['trl','postImages'])
            ->where(['status_id' => Constants::STATUS_ENABLED])
//            ->andWhere(new Expression('EXISTS (SELECT img.id FROM post_image img WHERE img.post_id = p.id)'))
//            ->andWhere(['kind_id' => Constants::KIND_FORUM])
            ->orderBy('published_at DESC');

        $qMainCount = clone $qMain;
        $qForumCount = clone $qForum;

        $pagesMain = new Pagination(['totalCount' => $qMainCount->count(), 'defaultPageSize' => 3]);
        $pagesForum = new Pagination(['totalCount' => $qForumCount->count(), 'defaultPageSize' => 4]);

        if($page > $pagesMain->pageCount){
            return null;
        }

        /* @var $posts Post[] */
        $posts = $qMain->offset($pagesMain->offset+3)->limit($pagesMain->limit)->all();
        $forumPosts = $qForum->offset($pagesForum->offset)->limit($pagesForum->limit)->all();

        return $this->renderPartial('_load', compact('posts','forumPosts'));
    }

    /**
     * Updates banner clicks and redirects to it's url
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionBannerRedirect($id)
    {
        /* @var $banner Banner */
        $banner = Banner::findOne((int)$id);

        if(empty($banner)){
            throw new NotFoundHttpException('Страница не найдена', 404);
        }

        $banner->clicks++;
        $banner->updated_at = date('Y-m-d H:i:s', time());
        $banner->update();

        return $this->redirect($banner->link);
    }

    /**
     * Temporary method to show all categories in list
     */
    public function actionRubricator()
    {
        $content = "";
        /* @var  $categories Category[] */
        $categories = Category::find()
            ->with([
                'trl',
                'parent.trl',
                'children.trl',
                'children.parent.trl',
                'parent.children.trl'
            ])
            ->where(['status_id' => Constants::STATUS_ENABLED, 'parent_category_id' => 0])
            ->orderBy('priority ASC')
            ->all();

        foreach ($categories as $cat){
            $content .= $cat->trl->name."\n";
            if(!empty($cat->children)){
                foreach ($cat->children as $child){
                    $content .= "--".$child->trl->name."\n";
                }
            }
        }

        return $this->renderContent(nl2br($content));
    }

    /**
     * Login with Facebook
     * @return \yii\web\Response
     * @throws NotAcceptableHttpException
     */
    public function actionFbLogin()
    {
        /* @var $social \kartik\social\Module */
        $social = Yii::$app->getModule('social');
        $fb = $social->getFb();

        try {
            $helper = $fb->getRedirectLoginHelper();
            $accessToken = $helper->getAccessToken();
        } catch(FacebookSDKException $e) {
            throw new NotAcceptableHttpException($e->getMessage(),'402');
        }

        if (isset($accessToken)) {
            $response = $fb->get('/me?fields=id,name,first_name,last_name,email,picture', $accessToken);
            $data = $response->getGraphUser()->asArray();

            /* @var $picture GraphPicture */
            $picture = $response->getGraphUser()->getPicture();

            if(!empty($data) && !empty($data['id'])){

                //try find
                /* @var $user User */
                $user = User::find()->where(['fb_user_id' => ArrayHelper::getValue($data,'id')])->one();

                if(empty($user)){
                    //create user
                    $user = new User();
                    $user->fb_user_id = ArrayHelper::getValue($data,'id');
                    $user->email = ArrayHelper::getValue($data,'email');
                    $user->name = ArrayHelper::getValue($data,'first_name');
                    $user->surname = ArrayHelper::getValue($data,'last_name');
                    $user->created_at = date('Y-m-d H:i:s',time());
                    $user->updated_at = date('Y-m-d H:i:s',time());
                    $user->username = ArrayHelper::getValue($data,'email',$user->fb_user_id);
                    $user->password_hash = Yii::$app->security->generatePasswordHash(Yii::$app->security->generateRandomString(6));
                    $user->auth_key = Yii::$app->security->generateRandomString();
                    $user->avatar_file = $picture->getUrl();
                    $user->status_id = Constants::STATUS_ENABLED;
                    $user->type_id = Constants::USR_TYPE_FB_AUTHORIZED;
                    $user->role_id = Constants::ROLE_REGULAR_USER;
                    $ok = $user->save();
                }else{
                    $ok = true;
                    $user->avatar_file = $picture->getUrl();
                    $user->updated_at = date('Y-m-d H:i:s',time());
                    $user->updated_by_id = $user->id;
                    $user->last_online_at = date('Y-m-d H:i:s',time());
                    $user->update();
                }

                //if saved or found - login
                if($ok){
                    Yii::$app->user->login($user);
                }

                //if admin or redactor - go to admin panel
                if($user->role_id == Constants::ROLE_REDACTOR || $user->role_id == Constants::ROLE_ADMIN){
                    return $this->redirect(Url::to(['/admin/main/index']));
                }
            }

        //log error if needed
        }elseif ($helper->getError()) {
            Help::log('auth.log',$helper->getError());
            Help::log('auth.log',$helper->getErrorCode());
            Help::log('auth.log',$helper->getErrorReason());
            Help::log('auth.log',$helper->getErrorDescription());
        }

        //back to main page
        return $this->redirect(Url::to(['/site/index']));
    }

    /**
     * Logout
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout(true);

        //back to main page
        return $this->redirect(Url::to(['/site/index']));
    }
}
