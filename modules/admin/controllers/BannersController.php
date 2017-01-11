<?php

namespace app\modules\admin\controllers;

use Yii;
use app\helpers\Sort;
use app\helpers\Constants;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class BannersController extends Controller
{
    function actionIndex()
    {
        $this->renderContent('There will be banner list');
    }

    public function actionPlaces()
    {
        $this->renderContent('There will be list of places');
    }
}