<?php
namespace app\widgets;

use app\helpers\Constants;
use app\helpers\Help;
use app\models\Category;
use app\models\Post;
use app\models\PostCategory;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class RelatedPostsWidget extends Widget
{
    /**
     * @var Post
     */
    public $post = null;

    /**
     * @var Category
     */
    public $categories = null;

    /**
     * @var int
     */
    public $exceptId = 0;

    /**
     * @var int
     */
    public $limit = 3;

    /**
     * @var Post[]
     */
    private $posts = [];


    public function init()
    {
        if(!empty($this->post)){
            $this->categories = $this->post->categories;
            $this->exceptId = $this->post->id;
        }

        if(!empty($this->categories)){
            $ids = ArrayHelper::map($this->categories,'id','id');
            $ids = array_values($ids);

            $pcs = PostCategory::find()
                ->alias('pc')
                ->where(['pc.category_id' => $ids])
                ->andWhere('pc.post_id != :exception',['exception' => $this->exceptId])
                ->groupBy('pc.post_id')
                ->with(['post','post.trl','post.comments', 'post.postImages'])
                ->joinWith('post as p')->andWhere(['p.status_id' => Constants::STATUS_ENABLED])
                ->offset(0)
                ->limit((int)$this->limit)
                ->all();

            if(!empty($pcs)){
                $this->posts = array_values(ArrayHelper::map($pcs,'post_id','post'));
            }

        }
    }

    public function run()
    {
        return $this->render('related_posts',['posts' => $this->posts]);
    }
}