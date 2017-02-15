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
    public $posts = [];
    public $limit = 4;
    public $label = '';

    public function init()
    {
        //TODO: initialization stuff
    }

    public function run()
    {
        return $this->render('post_horizontal',['posts' => $this->posts, 'label' => $this->label]);
    }
}