<?php

namespace app\components;

use Yii;
use yii\web\Controller as BaseController;
use yii\base\Module;
use yii\base\Action;
use yii\web\NotFoundHttpException;
use app\models\User;

class Controller extends BaseController
{
    /**
     * Redefine base constructor
     * @param string $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        //title of pages
        $this->view->title = "RusTurkey";

        //meta tags
        $this->view->registerMetaTag(['name' => 'description', 'content' => ""]);
        $this->view->registerMetaTag(['name' => 'keywords', 'content' => ""]);

        //open-graph meta tags
        $this->view->registerMetaTag(['property' => 'og:description', 'content' => ""]);
        $this->view->registerMetaTag(['property' => 'og:url', 'content' => ""]);
        $this->view->registerMetaTag(['property' => 'og:site_name', 'content' => ""]);
        $this->view->registerMetaTag(['property' => 'og:title', 'content' => ""]);
        $this->view->registerMetaTag(['property' => 'og:image', 'content' => ""]);
        $this->view->registerMetaTag(['property' => 'og:image:width', 'content' => '200']);
        $this->view->registerMetaTag(['property' => 'og:image:height', 'content' => '200']);

        //timezone
        date_default_timezone_set('Europe/Moscow');

        //enable foreign keys (for SQLITE database)
//        Yii::$app->db->createCommand("PRAGMA foreign_keys = ON")->execute();

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

        return parent::beforeAction($action);
    }
}