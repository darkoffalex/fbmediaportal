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
    public $categories = [];
    public $limit = 7;
    public $label = '';

    public function init()
    {
        if(empty(self::$posts)){
            $q = Post::find()
                ->where(['status_id' => Constants::STATUS_ENABLED]);

            if(!empty($this->categories)){
                $q->joinWith('postCategories as pc')->andWhere(['pc.category_id' => $this->categories]);
            }

            $q ->with(['trl'])
                ->orderBy('published_at DESC')
                ->limit($this->limit);

            self::$posts = $q->all();
        }

    }

    public function run()
    {
        return $this->render('post_list',['posts' => self::$posts, 'type' => 'turkey', 'label' => $this->label, ]);
    }
}