<?php
namespace app\widgets;

use app\helpers\Constants;
use app\models\Category;
use app\models\Post;
use yii\base\Widget;
use yii\helpers\Html;

class PopularPostWidget extends Widget
{
    /* @var Post */
    private static $posts = [];
    public $categories = [];
    public $minComments = 200;
    public $minDate = '2014.01.01 00:00:00';
    public $limit = 7;
    public $label = '';

    public function init()
    {
        if(empty(self::$posts)){

            $q = Post::find()->andWhere('last_comment_at > :minDate AND comment_count > :minComments AND status_id = :status',
                [
                    'minDate' => $this->minDate,
                    'minComments' => $this->minComments,
                    'status' => Constants::STATUS_ENABLED
                ]);

            if(!empty($this->categories)){
                $q->joinWith('postCategories as pc')->andWhere(['pc.category_id' => $this->categories]);
            }

            $q->with(['trl']);
            $q->orderBy('comment_count DESC');
            $q->limit($this->limit);

            self::$posts = $q->all();
        }
    }

    public function run()
    {
        return $this->render('post_list',['posts' => self::$posts, 'type' => 'popular', 'label' => $this->label, 'categories' => $this->categories]);
    }
}