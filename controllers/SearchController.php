<?php

namespace app\controllers;

use app\helpers\Constants;
use app\helpers\Help;
use app\models\Category;
use app\models\Post;
use yii\data\Pagination;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\GraphNodes\GraphPicture;
use Yii;
use app\components\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotAcceptableHttpException;
use app\models\User;

class SearchController extends Controller
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
     * Search results page
     * @return string
     */
    public function actionIndex()
    {
        $query = Yii::$app->request->get('q');
        $words = explode(' ',$query);

        if(!empty($query)){
            $q = Post::find()
                ->alias('p')
                ->andWhere(['p.status_id' => Constants::STATUS_ENABLED])
                ->joinWith('postSearchIndices as psi');

            foreach($words as $index => $word){
                $q->andWhere(['like','psi.text',$word]);
            }

            $q->orderBy('p.created_at DESC')->groupBy('p.id');

            $cq = clone $q;

            /* @var $pages Pagination */
            $pages = new Pagination(['totalCount' => $cq->count(), 'defaultPageSize' => 20]);
            $posts = $q->with(['trl', 'postImages', 'categories.trl', 'comments', 'author'])
                ->offset($pages->offset)
                ->limit($pages->limit)
                ->all();
        }

        return $this->render('index',compact('posts','pages'));
    }
}