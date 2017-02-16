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
                'parent.childrenActive.childrenActive',
                'childrenActive.childrenActive'
            ])->one();

        /* @var $children Category[] */
        $children = $category->getChildrenRecursive(true);
        $ids = ArrayHelper::map($children,'id','id');
        $ids = array_values($ids);
        $ids[] = $category->id;

        $currentIds = $ids;
        if(!empty($category->parent)){
            $siblingIds = array_values(ArrayHelper::map($category->parent->getChildrenRecursive(true),'id','id'));
        }else{
            $siblingIds = [];
        }


        /* @var $posts Post[] */
        $posts = Post::find()
            ->alias('p')
            ->joinWith('postCategories as pc')
            ->where(['pc.category_id' => $ids])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
//            ->andWhere(new Expression('EXISTS (SELECT img.id FROM post_image img WHERE img.post_id = p.id)'))
            ->orderBy(new Expression('IF((pc.category_id = :cat AND sticky_position > 0), sticky_position, 2147483647) ASC, IF(content_type_id = :lowestPriorityType, 2147483647, 0) ASC, p.published_at DESC',
                ['cat' => $category->id, 'lowestPriorityType' => Constants::CONTENT_TYPE_POST]))
            ->with(['trl', 'postImages.trl', 'author', 'comments'])
            ->limit(6)
            ->all();

        /* @var $forumPosts Post[] */
        $forumPosts = Post::find()
            ->alias('p')
            ->with(['trl','postImages'])
            ->joinWith('postCategories as pc')
            ->where(['pc.category_id' => $ids])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
//            ->andWhere(new Expression('EXISTS (SELECT img.id FROM post_image img WHERE img.post_id = p.id)'))
//            ->andWhere(['kind_id' => Constants::KIND_FORUM])
            ->orderBy('published_at DESC')
            ->offset(0)
            ->limit(4)
            ->all();

        return $this->render('show',compact('posts','forumPosts','category','currentIds','siblingIds'));
    }

    /**
     * Post-loading via ajax (while scrolling)
     * @param $id
     * @param int $page
     * @return null|string
     */
    public function actionPostLoad($id,$page = 1)
    {
        /* @var $category Category */
        $category = Category::find()
            ->where(['id' => (int)$id])
            ->with([
                'trl',
                'parent.childrenActive.childrenActive',
                'childrenActive.childrenActive'
            ])->one();

        /* @var $children Category[] */
        $children = $category->getChildrenRecursive(true);
        $ids = ArrayHelper::map($children,'id','id');
        $ids = array_values($ids);
        $ids[] = $category->id;

        $qMain = Post::find()
            ->alias('p')
            ->joinWith('postCategories as pc')
            ->where(['pc.category_id' => $ids])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->orderBy(new Expression('IF((pc.category_id = :cat AND sticky_position > 0), sticky_position, 2147483647) ASC, IF(type_id = :lowestPriorityType, 2147483647, 0) ASC, p.published_at DESC',
                ['cat' => $category->id, 'lowestPriorityType' => Constants::CONTENT_TYPE_POST]))
            ->with(['trl', 'postImages.trl', 'author', 'comments']);

        $qForum = Post::find()
            ->alias('p')
            ->with(['trl','postImages'])
            ->joinWith('postCategories as pc')
            ->where(['pc.category_id' => $ids])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
//            ->andWhere(new Expression('EXISTS (SELECT img.id FROM post_image img WHERE img.post_id = p.id)'))
//            ->andWhere(['kind_id' => Constants::KIND_FORUM])
            ->orderBy('published_at DESC');

        $qMainCount = clone $qMain;
        $qForumCount = clone $qForum;

        $pagesMain = new Pagination(['totalCount' => $qMainCount->count(), 'defaultPageSize' => 3]);
        $pagesForum = new Pagination(['totalCount' => $qForumCount->count(), 'defaultPageSize' => 4]);

        if($page > $pagesMain->pageCount){
            return null;
        }

        /* @var $posts Post[] */
        $posts = $qMain->offset($pagesMain->offset+3)->limit($pagesMain->limit)->all();

        if($page > $pagesForum->pageCount){
            $forumPosts = [];
        }else{
            $forumPosts = $qForum->offset($pagesForum->offset)->limit($pagesForum->limit)->all();
        }

        return $this->renderPartial('/site/_load', compact('posts','forumPosts'));
    }
}