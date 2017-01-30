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
        //getting post
        /* @var $post Post */
        $post = Post::find()->with(['categories','trl'])->where(['id' => $id])->one();

        //getting comments
        /* @var $comments Comment */
        $q = Comment::find()->where(['post_id' => $post->id])->orderBy(new Expression('IF(answer_to_id, answer_to_id, id), answer_to_id, created_at ASC'));
        $cq = clone $q;
        $pages = new Pagination(['totalCount' => $cq->count(), 'defaultPageSize' => 20]);
        $comments = $q ->with(['author','parent'])->offset($pages->offset)->limit($pages->limit)->all();


        if(empty($post)){
            throw new NotFoundHttpException('Страница не найдена', 404);
        }

        return $this->render('show',compact('post','comments','pages'));
    }

    /**
     * Leaving comment for post
     * @param null $pid
     * @param null $cid
     * @return array|string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionAddComment($pid = null,$cid = null)
    {
        if(Yii::$app->user->isGuest){
            return $this->renderPartial('_please_login');
        }

        /* @var $post Post */
        /* @var $comment Comment */
        /* @var $user User */
        $post = Post::find()->where(['id' => $pid])->one();
        $comment = Comment::find()->where(['id' => $cid])->one();
        $user = Yii::$app->user->identity;

        $post = !empty($comment) ? $comment->post : $post;

        if(empty($post)){
            throw new NotFoundHttpException('Не уадлось найти пост', 404);
        }

        $model = new Comment();
        $model->isFrontend = true;

        //ajax validation
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

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

                    $this->redirect(Yii::$app->request->referrer);
                }
            }
        }

        return $this->renderAjax('_comment',compact('model','post','comment'));
    }
}