<?php

namespace app\components;

use app\helpers\Constants;
use app\helpers\Help;
use app\models\Banner;
use app\models\BannerDisplay;
use app\models\Category;
use app\models\CommonSettings;
use Yii;
use yii\caching\Cache;
use yii\db\Expression;
use yii\web\Controller as BaseController;
use yii\base\Module;
use yii\base\Action;
use yii\web\NotFoundHttpException;
use app\models\User;

class Controller extends BaseController
{

    /**
     * @var CommonSettings
     */
    public $commonSettings = null;

    /**
     * @var Category[]
     */
    public $mainMenu = [];

    /**
     * @var Banner[][]
     */
    public $banners = [];

    /**
     * Redefine base constructor
     * @param string $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        //title of pages
        $this->view->title = "RusTurkey.com";

        //timezone
        date_default_timezone_set('Europe/Moscow');

        //base constructor
        parent::__construct($id,$module,$config);
    }

    /**
     * Run before every action on frontend part
     * @param Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        /* @var $user User */
        $user = !Yii::$app->user->isGuest ? Yii::$app->user->identity : null;

        //Update the last visit time
        if(!empty($user)){
            $user->last_online_at = date('Y-m-d H:i:s', time());
            $user->update();
        }

        //Get common settings if empty
        if(empty($this->commonSettings)){
            $this->commonSettings = Help::cquery(function($db){return CommonSettings::find()->one();},true);
            if(empty($this->commonSettings)){
                $this->commonSettings = new CommonSettings();
                $this->commonSettings->save();
            }
        }

        //Get main menu
        $qMenu = Category::find()
            ->where(['status_id' => Constants::STATUS_ENABLED, 'parent_category_id' => 0])
            ->with(['trl', 'childrenActive.trl', 'childrenActive.childrenActive.trl'])
            ->orderBy('priority ASC');
        $this->mainMenu = Help::cquery(function($db)use($qMenu){return $qMenu->all();},true);


        //Get all banners
        $qBannerDisplays = BannerDisplay::find()
            ->with(['banner','place'])
            ->where(new Expression('start_at < :cur AND end_at > :cur',['cur' => date('Y-m-d H:i:s',time())]));
        $bannerDisplays = Help::cquery(function($db)use($qBannerDisplays){return $qBannerDisplays->all();},true);

        /* @var $bannerDisplays BannerDisplay[] */
        foreach ($bannerDisplays as $bannerDisplay){
            $this->banners[$bannerDisplay->place->alias][] = $bannerDisplay->banner;
        }

        //clear the cache
//        Yii::$app->cache->flush();

        return parent::beforeAction($action);
    }
}