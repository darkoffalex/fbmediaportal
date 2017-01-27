<?php
namespace app\widgets;

use app\helpers\Constants;
use app\models\Category;
use app\models\Post;
use yii\base\Widget;
use yii\helpers\Html;

class MostCommentedWidget extends Widget
{
    /* @var Post */
    private $posts = [];

    public function init()
    {
        //TODO: get posts which has more than 10 new comments
        $this->posts = Post::find()
            ->with('comments')
            ->where(['status_id' => Constants::STATUS_ENABLED])
            ->orderBy('created_at ASC')
            ->limit(5)->all();
    }

    public function run()
    {
        return $this->render('most_commented',['posts' => $this->posts]);
    }
}