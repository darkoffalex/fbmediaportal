<?php

namespace app\modules\admin;

use app\helpers\Help;
use app\models\User;
use Yii;
use yii\helpers\Url;

/**
 * admin module definition class
 */
class AdminModule extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\admin\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->layoutPath = "@app/modules/admin/views/layouts";
        $this->viewPath = "@app/modules/admin/views";
        $this->layout = 'main';
    }

    /**
     * Выполняется перед каждым action'ом
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        if(!parent::beforeAction($action)){
            return false;
        }

        /* @var $user User */
        $user = !Yii::$app->user->isGuest ? Yii::$app->user->identity : null;

        if((empty($user) || !$user->hasAdminAccess()) && $action->id != 'login'){
            Yii::$app->response->redirect(Url::to(['/admin/main/login']));
            return false;
        }

        return true;
    }
}
