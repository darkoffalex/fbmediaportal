<?php

namespace app\widgets;

use app\models\Comment;
use app\models\Post;
use app\models\User;
use yii\base\Widget;
use Yii;
use yii\data\Pagination;

class NonCacheComments extends Widget
{
    /**
     * @var Comment[]
     */
    public $comments = [];

    /**
     * @var Post
     */
    public $post = null;

    /**
     * @var User
     */
    public $user = null;

    public function init()
    {
        //get post
        $this->post = Post::findOne((int)Yii::$app->request->get('id'));

        //user
        $this->user = Yii::$app->user->identity;

        //get comments for current post (first 10)
        $q = Comment::find()
            ->where('answer_to_id IS NULL OR answer_to_id = 0')
            ->andWhere(['post_id' => $this->post->id])
            ->with([
                'author',
                'children',
            ])
            ->orderBy('created_at ASC');

        //get dynamic post part
        $cq = clone $q;
        $pages = new Pagination(['totalCount' => $cq->count(), 'defaultPageSize' => 10]);
        $this->comments = $q->offset($pages->offset)->limit($pages->limit)->all();
    }

    public function run()
    {
        return $this->render('_non_cache_comments',['comments' => $this->comments, 'post' => $this->post, 'user' => $this->user]);
    }
}