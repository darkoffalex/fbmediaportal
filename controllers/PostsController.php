<?php

namespace app\controllers;

use app\helpers\Constants;
use app\helpers\Help;
use app\models\Category;
use app\models\Comment;
use app\models\Post;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\GraphNodes\GraphPicture;
use Yii;
use app\components\Controller;
use yii\bootstrap\ActiveForm;
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotAcceptableHttpException;
use app\models\User;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PostsController extends Controller
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
     * Show single post
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionShow($id)
    {
        /* @var $user User */
        $user = Yii::$app->user->identity;

        //getting post
        /* @var $post Post */
        $post = Post::find()->with([
            'trl',
            'categories.trl',
            'categories.parent.trl',
            'categories.parent.parent.trl',
            'author',
            'postImages',
        ])->where(['id' => $id, 'status_id' => Constants::STATUS_ENABLED])->one();


        if(empty($post)){
            throw  new NotFoundHttpException('Страница не найдена',404);
        }

        //store post's category ids
        $ids = array_values(ArrayHelper::map($post->categories,'id','id'));
        $this->categoryIds = $ids;

        //C O M M E N T  A D D I N G
        $newComment = new Comment();
        $newComment->isFrontend = true;

        if(Yii::$app->request->isPost && $newComment->load(Yii::$app->request->post())){
            $newComment->post_id = $post->id;
            $newComment->author_id = Yii::$app->user->id;
            if(!empty($newComment)) $newComment->answer_to_id = $newComment->id;

            if($newComment->validate()){
                $newComment->created_by_id = Yii::$app->user->id;
                $newComment->updated_by_id = Yii::$app->user->id;
                $newComment->created_at = date('Y-m-d h:i:s',time());
                $newComment->updated_at = date('Y-m-d h:i:s',time());

                if($newComment->save()){
                    $post->updated_at = date('Y-m-d h:i:s',time());
                    $post->updated_by_id = Yii::$app->user->id;
                    $post->update();

                    $user->refresh();
                    $user->counter_comments = count($user->comments);
                    $user->update();

                    if(!empty($user->fb_user_id)){
                        //TODO: Apply changes in FB
                    }
                }
            }
        }

        $q = Comment::find()
            ->where('answer_to_id IS NULL OR answer_to_id = 0')
            ->andWhere(['post_id' => $post->id])
            ->with([
                'author',
                'children',
            ])
            ->orderBy('created_at ASC');

        $cq = clone $q;
        $pages = new Pagination(['totalCount' => $cq->count(), 'defaultPageSize' => 10]);
        $comments = $q->offset($pages->offset)->limit($pages->limit)->all();

        return $this->render('show',compact('post','comments','newComment'));
    }


    /**
     * Loads additional children comments
     * @param $id
     * @return string
     */
    public function actionChildrenComments($id)
    {
        $comments = Comment::find()
            ->where('answer_to_id = :id', ['id' => $id])
            ->with([
                'author',
                'children',
            ])
            ->orderBy('created_at ASC')
            ->all();

        return $this->renderPartial('_comments_children', compact('comments'));
    }

    /**
     * Load comments
     * @param $id
     * @param $page
     * @return string
     */
    public function actionCommentPostLoad($id,$page = 1)
    {
        $q = Comment::find()
            ->where('answer_to_id IS NULL OR answer_to_id = 0')
            ->andWhere(['post_id' => $id])
            ->with([
                'author',
                'children',
            ])
            ->orderBy('created_at ASC');

        $cq = clone $q;
        $pages = new Pagination(['totalCount' => $cq->count(), 'defaultPageSize' => 10]);

        if($page > $pages->pageCount){
            return null;
        }

        $comments = $q->offset($pages->offset)->limit($pages->limit)->all();

        return $this->renderPartial('_comments_load', compact('comments'));
    }

    /**
     * Leaving comment for post
     * @param null $cid
     * @return array|string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionAddChildComment($cid)
    {
        if(Yii::$app->user->isGuest){
            return $this->renderPartial('_please_login');
        }

        /* @var $post Post */
        /* @var $comment Comment */
        /* @var $user User */
        $comment = Comment::find()->where(['id' => $cid])->one();
        $user = Yii::$app->user->identity;
        $post = $comment->post;

        if(empty($post)){
            throw new NotFoundHttpException('Страница не найдена', 404);
        }

        $model = new Comment();
        $model->isFrontend = true;

        if(Yii::$app->request->isPost && $model->load(Yii::$app->request->post())){
            $model->post_id = $post->id;
            $model->author_id = Yii::$app->user->id;
            if(!empty($comment)) $model->answer_to_id = $comment->id;

            if($model->validate()){
                $model->created_by_id = Yii::$app->user->id;
                $model->updated_by_id = Yii::$app->user->id;
                $model->created_at = date('Y-m-d h:i:s',time());
                $model->updated_at = date('Y-m-d h:i:s',time());

                if($model->save()){
                    $post->updated_at = date('Y-m-d h:i:s',time());
                    $post->updated_by_id = Yii::$app->user->id;
                    $post->update();

                    $user->refresh();
                    $user->counter_comments = count($user->comments);
                    $user->update();

                    if(!empty($user->fb_user_id)){
                        //TODO: Apply changes in FB
                    }
                }
            }
        }

        return $this->actionChildrenComments($cid);
    }
}