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
//                'children.trl',
//                'children.children.trl',
//                'parent',
//                'parent.trl',
//                'parent.parent.trl'
            ])->one();

        /* @var $children Category[] */
        $children = $category->getChildrenRecursive(true);
        $ids = ArrayHelper::map($children,'id','id');
        $ids = array_values($ids);
        $ids[] = $category->id;

        /* @var $posts Post[] */
        $posts = Post::find()
            ->alias('p')
            ->joinWith('postCategories as pc')
            ->where(['pc.category_id' => $ids])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->orderBy(new Expression('IF((pc.category_id = :cat AND sticky_position > 0), sticky_position, 2147483647) ASC, p.published_at DESC',['cat' => $category->id]))
            ->with(['trl', 'postImages.trl', 'categories.trl', 'comments', 'author'])
            ->limit(6)
            ->all();

        return $this->render('show',compact('posts','category'));
    }
}