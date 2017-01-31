<?php
namespace app\widgets;

use app\helpers\Constants;
use app\models\Category;
use app\models\Post;
use yii\base\Widget;
use yii\helpers\Html;

class LatestPostsWidget extends Widget
{
    /* @var Post */
    private $posts = [];

    public function init()
    {
        $this->posts = Post::find()
            ->where(['status_id' => Constants::STATUS_ENABLED])
            ->with('postImages')
            ->orderBy('created_at DESC')
            ->limit(5)
            ->all();
    }

    public function run()
    {
        return $this->render('latest_posts',['posts' => $this->posts]);
    }
}