<?php

namespace app\controllers;

use app\helpers\Constants;
use app\helpers\Help;
use app\models\Category;
use app\models\Post;
use Yii;
use app\components\ControllerEx;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class MainController extends ControllerEx
{
    /**
     * Entry point
     */
    public function actionIndex()
    {
        return $this->actionCategory();
    }

    /**
     * Render category page
     * @param null $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionCategory($id = null)
    {
        //temporary set set-layout
        $this->layout = 'main-alt';

        //view settings
        $this->view->title = "RusTurkey";

        //current category (can be empty, then show all items)
        /* @var $category Category */
        $category = null;

        //category ids which should be used for finding posts (empty for searching whole posts)
        $currentIds = [];
        //siblings categories (second priority for ordering)
        $siblingIds = [];

        //if ID set - try to get category
        if(!empty($id)){
            $catQuery = Category::find()
                ->where(['id' => $id, 'status_id' => Constants::STATUS_ENABLED])
                ->with([
                    'trl',
                    'parent.childrenActive.childrenActive',
                    'childrenActive.childrenActive'
                ]);

            $category = Help::cquery(function($db)use($catQuery){return $catQuery->one();},false);

            if(empty($category)){
                throw new NotFoundHttpException('Рубрика не найдена', 404);
            }
        }

        //get all category ids which should be included in search query
        if(!empty($category)){

            //set meta data
            $this->view->title = !empty($category) ? $category->trl->name : $this->view->title;
            $this->view->registerMetaTag(['name' => 'description', 'content' => !empty($category->trl->meta_description) ? $category->trl->meta_description : $this->commonSettings->meta_description]);
            $this->view->registerMetaTag(['name' => 'keywords', 'content' => !empty($category->trl->meta_keywords) ? $category->trl->meta_keywords : $this->commonSettings->meta_keywords]);

            //open-graph meta tags
//            $this->view->registerMetaTag(['property' => 'og:description', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:url', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:site_name', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:title', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:image', 'content' => ""]);
//            $this->view->registerMetaTag(['property' => 'og:image:width', 'content' => '200']);
//            $this->view->registerMetaTag(['property' => 'og:image:height', 'content' => '200']);

            /* @var $children Category[] */
            $children = $category->getChildrenRecursive(true);
            $currentIds = array_values(ArrayHelper::map($children,'id','id'));
            $currentIds[] = $category->id;

            /* @var $siblings Category[] */
            $siblings = !empty($category->parent) ? $category->parent->getChildrenRecursive(true) : [];
            $siblingIds = !empty($siblings) ? array_values(ArrayHelper::map($siblings,'id','id')) : [];
            foreach ($siblingIds as $index => $id){
                if(in_array($id,$currentIds)){
                    unset($siblingIds[$index]);
                }
            }
        }

        $mainPostsQuery = Post::findSorted(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->with(['trl', 'postImages.trl', 'author', 'comments'])
            ->andWhere(['status_id' => Constants::STATUS_ENABLED])
            ->andWhere(new Expression('(kind_id IS NULL OR kind_id != :except)',['except' => Constants::KIND_FORUM]));

        $forumPostsQuery = Post::findSorted(!empty($category) ? $id : null,$currentIds,$siblingIds)
            ->with(['trl', 'postImages.trl', 'author', 'comments']);
//            ->andWhere(['status_id' => Constants::STATUS_ENABLED, 'kind_id' => Constants::KIND_FORUM]);

        $mainPostsQuery->limit('15');
        $forumPostsQuery->limit('4');

        //get main and forum posts posts for first page (next pages will be loaded via ajax)
        $mainPosts = Help::cquery(function($db)use($mainPostsQuery){return $mainPostsQuery->all();},false);
        $forumPosts = Help::cquery(function($db)use($forumPostsQuery){return $forumPostsQuery->all();},false);

        //rendering page
//        return $this->renderContent('test');
        return $this->render('category',compact('mainPosts','forumPosts','category'));
    }
}