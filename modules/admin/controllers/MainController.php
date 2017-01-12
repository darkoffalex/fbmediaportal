<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\helpers\Url;
use app\models\User;
use app\models\LoginForm;
use yii\web\Controller;

/**
 * Default controller for the `admin` module
 */
class MainController extends Controller
{
    /**
     * Redirects to the main page
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect(Url::to(['/admin/users/index']));
    }

    /**
     * Login through form
     * @return string
     */
    public function actionLogin()
    {
        $this->layout = 'main-login';
        $this->view->title = Yii::t('admin','Login to admin panel');

        /* @var $identity User */
        $identity = Yii::$app->user->identity;
        if (!empty($identity) && $identity->hasAdminAccess()) {
            return $this->redirect(Url::to(['/admin/main/index']));
        }
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(Url::to(['/admin/main/index']));
        }

        return $this->render('login', compact('model'));
    }

    /**
     * Logout
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(Url::to(['/admin/main/login']));
    }
}
