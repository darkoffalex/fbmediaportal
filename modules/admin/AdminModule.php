<?php

namespace app\modules\admin;

use app\helpers\Constants;
use app\helpers\Help;
use app\models\User;
use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

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

        //Update the last visit time
        if(!empty($user)){
            $user->last_online_at = date('Y-m-d H:i:s', time());
            $user->update();

            //if redactor trying to access users controller - sent to categories
            if($action->controller->id == 'users' && $user->role_id == Constants::ROLE_REDACTOR){
                Yii::$app->response->redirect(Url::to(['/admin/categories/index']));
                return false;
            }
        }

        //Redirect to login page if not authenticated
        if((empty($user) || !$user->hasAdminAccess()) && $action->id != 'login'){
            Yii::$app->response->redirect(Url::to(['/admin/main/login']));
            return false;
        }

        return true;
    }
}
