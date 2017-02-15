<?php
namespace app\widgets;

use app\helpers\Constants;
use app\models\Category;
use app\models\Post;
use yii\base\Widget;
use yii\db\Expression;
use yii\helpers\Html;

class ForumPostsWidget extends Widget
{
    /* @var Post */
    private static $posts = [];
    public $limit = 4;
    public $label = '';

    public function init()
    {
        if(empty(self::$posts)){
            self::$posts = Post::find()
                ->alias('p')
                ->where(['status_id' => Constants::STATUS_ENABLED])
                ->andWhere(new Expression('EXISTS (SELECT img.id FROM post_image img WHERE img.post_id = p.id)'))
                ->with(['trl','postImages'])
                ->orderBy('published_at DESC')
                ->limit($this->limit)
                ->all();
        }

    }

    public function run()
    {
        return $this->render('post_horizontal',['posts' => self::$posts, 'label' => $this->label]);
    }
}