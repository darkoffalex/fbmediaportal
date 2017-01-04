<?php

namespace app\controllers;

use Yii;
use app\components\Controller;

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
        return $this->render('index');
    }

    public function actionHhhh()
    {
        echo "Hello world!";
        exit();
    }

}
