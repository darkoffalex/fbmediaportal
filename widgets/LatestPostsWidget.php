<?php
namespace app\widgets;

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
        $this->posts = Post::find()->with('postImages')->orderBy('created_at DESC')->limit(5)->all();
    }

    public function run()
    {
        return $this->render('latest_posts',['posts' => $this->posts]);
    }
}