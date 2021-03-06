<?php

namespace app\controllers;

use app\helpers\Constants;
use app\helpers\Help;
use app\models\Banner;
use app\models\Category;
use app\models\Post;
use app\models\UserTimeLine;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\GraphNodes\GraphPicture;
use Yii;
use app\components\Controller;
use yii\data\Pagination;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\NotAcceptableHttpException;
use app\models\User;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
     * Clear cache
     * @return Response
     */
    public function actionCc()
    {
        //if need back to previous page
        $back = Yii::$app->request->get('back');

        //if need full delete
        $full = Yii::$app->request->get('full');

        //clear standard cache
        Yii::$app->cache->flush();

        //skip settings
        $skip = array('.', '..', '.gitignore','cropped','thumbnails');

        //if need full delete - do not skip cropped and resized pics folders
        if($full){
            unset($skip[3]);
            unset($skip[4]);
        }

        //path to assets and runtime directories
        $assets = scandir(Yii::getAlias('@webroot/assets'));
        $runtime = scandir(Yii::getAlias('@app/runtime'));

        //clear assets
        foreach($assets as $entry) {
            if(!in_array($entry, $skip)){
                $path = Yii::getAlias('@webroot/assets/'.$entry);
                FileHelper::removeDirectory($path);
                if(!$back) echo $path." removed <br>";
            }
        }

        //clear runtime
        foreach($runtime as $entry) {
            if(!in_array($entry, $skip)){
                $path = Yii::getAlias('@app/runtime/'.$entry);
                FileHelper::removeDirectory($path);
                if(!$back) echo $path." removed <br>";
            }
        }

        if(!$back){
            exit('Finished');
        }else{
            return $this->redirect(Yii::$app->request->referrer);
        }

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
     * Generates site-map
     * @return mixed|string
     */
    public function actionSiteMap()
    {
        if (!$xmlSiteMap = Yii::$app->cache->get('sitemap')) {
            $urls = array();

            $posts = (new Query())
                ->select('p.id, p.updated_at, trl.name')
                ->from('post as p')
                ->leftJoin('post_trl as trl', 'trl.post_id = p.id AND trl.lng = :lng',['lng' => Yii::$app->language])
                ->where('p.status_id = :status', ['status' => Constants::STATUS_ENABLED])
                ->orderBy('published_at DESC')
                ->all();

            foreach ($posts as $post) {
                $item = [
                    'updated_at' => $post['updated_at'],
                    'id' => $post['id'],
                    'url' => Yii::$app->urlManager->createUrl(['/main/post','id' => $post['id'], 'title' => Help::slug($post['name'])]),
                    'freq' => 'daily'
                ];
                $urls[] = $item;
            }

            $xmlSiteMap = $this->renderPartial('sitemap', array(
                'host' => Yii::$app->request->hostInfo,
                'urls' => $urls,
            ));

            Yii::$app->cache->set('sitemap', $xmlSiteMap, 3600*24*7);
        }

        header('Content-Type: application/xml');
        echo $xmlSiteMap;
    }

    /**
     * Login with Facebook
     * @return \yii\web\Response
     * @throws NotAcceptableHttpException
     */
    public function actionFbLogin()
    {

        if(!session_id()) {
            session_start();
        }

        $redirectUrl = Yii::$app->session->getFlash('last-url',Url::to(['/main/profile']));

        $fb = new Facebook([
            'app_id' => Yii::$app->params['facebook']['app_id'],
            'app_secret' => Yii::$app->params['facebook']['app_secret'],
        ]);

        try {
            $helper = $fb->getRedirectLoginHelper();
            $accessToken = $helper->getAccessToken(Url::to(['/site/fb-login'],true));
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
                    $user->fb_auth_token = $accessToken->getValue();
                    $ok = $user->save();
                }else{
                    $ok = true;
                    $user->avatar_file = $picture->getUrl();
                    $user->updated_at = date('Y-m-d H:i:s',time());
                    $user->updated_by_id = $user->id;
                    $user->last_online_at = date('Y-m-d H:i:s',time());
                    $user->fb_auth_token = $accessToken->getValue();
                    $user->update();
                }

                //if saved or found - login
                if($ok){
                    Yii::$app->user->login($user);
                }

                //if admin or redactor - go to admin panel
                if($user->role_id == Constants::ROLE_REDACTOR || $user->role_id == Constants::ROLE_ADMIN){
                    return $this->redirect(Url::to(['/admin/main/index']));
                }else{
                    return $this->redirect($redirectUrl);
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
        return $this->redirect(Url::to(['/main/index']));
    }

    /**
     * Logout
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout(true);

        //back to main page
        return $this->redirect(Url::to(['/main/index']));
    }
}
