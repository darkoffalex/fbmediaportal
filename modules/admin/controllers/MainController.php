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
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Логин админ-пользователя при помощи формы
     * @return string
     */
    public function actionLogin()
    {
        $this->layout = 'main-login';
        $this->view->title = "Вход в админ панель";

        /* @var $identity User */
        $identity = Yii::$app->user->identity;
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(Url::to(['/admin/main/index']));
        }
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(Url::to(['/admin/main/index']));
        }

        return $this->render('login', compact('model'));
    }

    /**
     * Логаут
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(Url::to(['/admin/main/login']));
    }
}
