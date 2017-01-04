<?php

namespace app\modules\admin;

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

        if(Yii::$app->user->isGuest && $action->id != 'login'){
            Yii::$app->response->redirect(Url::to(['/admin/main/login']));
            return false;
        }

        return true;
    }
}
