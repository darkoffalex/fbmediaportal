<?php
namespace app\widgets;

use app\helpers\Constants;
use app\models\Category;
use app\models\Post;
use yii\base\Widget;
use yii\helpers\Html;

class TurkeyPostsWidget extends Widget
{
    /* @var Post */
    private static $posts = [];
    public $limit = 7;
    public $label = '';

    public function init()
    {
        if(empty(self::$posts)){
            self::$posts = Post::find()
                ->where(['status_id' => Constants::STATUS_ENABLED])
                ->with(['trl'])
                ->orderBy('published_at DESC')
                ->limit($this->limit)
                ->all();
        }

    }

    public function run()
    {
        return $this->render('post_list',['posts' => self::$posts, 'type' => 'turkey', 'label' => $this->label]);
    }
}