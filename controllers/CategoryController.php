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

class CategoryController extends Controller
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
     * Display category
     * @param $id
     * @return string
     */
    public function actionShow($id)
    {

        /* @var $category Category */
        $category = Category::find()
            ->where(['id' => (int)$id])
            ->with([
                'trl',
                'children.trl',
                'children.children.trl',
                'parent',
                'parent.trl',
                'parent.parent.trl'
            ])->one();

        /* @var $children Category[] */
        $children = $category->getChildrenRecursive(true);
        $ids = ArrayHelper::map($children,'id','id');
        $ids = array_values($ids);
        $ids[] = $category->id;

        $q = Post::find()
            ->alias('p')
            ->joinWith('postCategories as pc')
            ->where(['pc.category_id' => $ids])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->orderBy('p.created_at DESC');
        $cq = clone $q;

        /* @var $pages Pagination */
        $pages = new Pagination(['totalCount' => $cq->count(), 'defaultPageSize' => 20]);
        $posts = $q->with(['trl', 'postImages', 'categories.trl', 'comments', 'author'])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        /*
        foreach($posts as $post){
            $categoryPosition = ArrayHelper::map($post->postCategories,'category_id','sticky_position');
            $post->stickyFlag = ArrayHelper::getValue($categoryPosition,$category->id,PHP_INT_MAX);
        }
        */

//        ArrayHelper::multisort($posts,['stickyFlag','created_at'],[SORT_ASC,SORT_DESC]);

        return $this->render('show',compact('posts','category','pages'));
    }
}