<?php

namespace app\components;

use app\models\CommonSettings;
use Yii;
use yii\web\Controller as BaseController;
use yii\base\Module;
use yii\base\Action;
use yii\web\NotFoundHttpException;
use app\models\User;

class Controller extends BaseController
{
    /**
     * @var int[]
     */
    public $categoryIds = [];

    /**
     * @var CommonSettings
     */
    public $commonSettings = null;

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

        //Get common settings if empty
        if(empty($this->commonSettings)){
            $this->commonSettings = CommonSettings::find()->one();
            if(empty($this->commonSettings)){
                $this->commonSettings = new CommonSettings();
                $this->commonSettings->save();
            }
        }

        return parent::beforeAction($action);
    }
}