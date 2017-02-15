<?php
namespace app\widgets;

use app\helpers\Constants;
use app\models\Category;
use app\models\Post;
use yii\base\Widget;
use yii\db\Expression;
use yii\helpers\Html;

class LatestPostsWidget extends Widget
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
                ->where(new Expression('status_id = :status AND (kind_id != :kind OR kind_id IS NULL)', ['status' => Constants::STATUS_ENABLED, 'kind' => Constants::KIND_FORUM]));

            if(!empty($this->categories)){
                $q->joinWith('postCategories as pc')->andWhere(['pc.category_id' => $this->categories]);
            }

            $q->with(['trl'])
                ->orderBy('published_at DESC')
                ->limit($this->limit);

            self::$posts = $q->all();
        }

    }

    public function run()
    {
        return $this->render('post_list',['posts' => self::$posts, 'type' => 'latest', 'label' => $this->label, 'categories' => $this->categories]);
    }
}