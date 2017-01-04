<?php

namespace app\components;

use yii\web\Controller as BaseController;
use yii\base\Module;
use yii\base\Action;
use yii\web\NotFoundHttpException;

class Controller extends BaseController
{
    /**
     * Переопределить конструктор
     * @param string $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        //заголовок страниц
        $this->view->title = "Мой сайт";

        //мета-теги
        $this->view->registerMetaTag(['name' => 'description', 'content' => ""]);
        $this->view->registerMetaTag(['name' => 'keywords', 'content' => ""]);

        //open-graph мета-теги
        $this->view->registerMetaTag(['property' => 'og:description', 'content' => ""]);
        $this->view->registerMetaTag(['property' => 'og:url', 'content' => ""]);
        $this->view->registerMetaTag(['property' => 'og:site_name', 'content' => ""]);
        $this->view->registerMetaTag(['property' => 'og:title', 'content' => ""]);
        $this->view->registerMetaTag(['property' => 'og:image', 'content' => ""]);
        $this->view->registerMetaTag(['property' => 'og:image:width', 'content' => '200']);
        $this->view->registerMetaTag(['property' => 'og:image:height', 'content' => '200']);

        //временная зона
        date_default_timezone_set('Europe/Moscow');

        //включить внешние ключи (для операций со SQLITE базой)
//        Yii::$app->db->createCommand("PRAGMA foreign_keys = ON")->execute();

        //базовый конструктор
        parent::__construct($id,$module,$config);
    }

    /**
     * Выполнять перед каждым action'ом
     * @param Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        //TODO: здесь код что будет выполняться пепед каждый действием
        return parent::beforeAction($action);
    }
}