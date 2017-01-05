<?php

namespace app\modules\admin\controllers;

use app\helpers\Sort;
use app\helpers\Help;
use app\models\Category;
use app\models\Language;
use app\models\PostSearch;
use kartik\form\ActiveForm;
use Yii;
use app\helpers\Constants;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PostsController extends Controller
{
    /**
     * Render list of all posts that were imported or created by hand
     * @param null $lng
     * @return string
     */
    public function actionIndex($lng = null)
    {
        /* @var $languages Language[] */
        $languages = Language::find()->all();
        $lng = empty($lng) ? $languages[0]->prefix :$lng;


        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$lng);
        return $this->render('index', compact('searchModel','dataProvider','lng'));
    }
}