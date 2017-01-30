<?php

namespace app\controllers;

use app\helpers\Constants;
use app\helpers\Help;
use app\models\Category;
use app\models\Post;
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
                'postsActive',
                'postsActive.trl',
                'postsActive.postImages',
                'postsActive.postCategories',
                'postsActive.comments',
                'postsActive.author',
                'children.trl',
                'children.postsActive',
                'children.postsActive.trl',
                'children.postsActive.postImages',
                'children.postsActive.postCategories',
                'children.postsActive.comments',
                'children.postsActive.author',
                'children.children.trl',
                'children.children.postsActive',
                'children.children.postsActive.trl',
                'children.children.postsActive.postImages',
                'children.children.postsActive.postCategories',
                'children.children.postsActive.comments',
                'children.children.postsActive.author'
            ])->one();


        /* @var $posts Post[] */
        $posts = $category->getPostsRecursive(true);

        foreach($posts as $post){
            $categoryPosition = ArrayHelper::map($post->postCategories,'category_id','sticky_position');
            $post->stickyFlag = ArrayHelper::getValue($categoryPosition,$category->id,PHP_INT_MAX);
        }

//        ArrayHelper::multisort($posts,['stickyFlag','created_at'],[SORT_ASC,SORT_DESC]);

        return $this->render('show',compact('posts','category'));
    }
}