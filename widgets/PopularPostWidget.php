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
    public $currentIds = [];
    public $siblingIds = [];
    public $minComments = 200;
    public $minDate = '2014.01.01 00:00:00';
    public $limit = 7;
    public $label = '';

    public function init()
    {
        if(empty(self::$posts)){

            //get all popular posts
            $q = Post::find();

            $q->andWhere('last_comment_at > :minDate AND comment_count > :minComments AND status_id = :status',
                [
                    'minDate' => $this->minDate,
                    'minComments' => $this->minComments,
                    'status' => Constants::STATUS_ENABLED
                ]);

            //if need filter by current category and it's children
            if(!empty($this->currentIds)){
                $q->joinWith('postCategories as pc');

                $countQ = clone $q;
                $countQ->andWhere(['pc.category_id' => $this->currentIds]);

                //if lack of posts
                if($countQ->count() < 7 && !empty($this->siblingIds)){
                    $q->andWhere(['pc.category_id' => $this->siblingIds]);
                }else{
                    $q->andWhere(['pc.category_id' => $this->currentIds]);
                }
            }


            $q->with(['trl']);
//            $q->distinct();
            $q->orderBy('comment_count DESC');
            $q->limit($this->limit);

            self::$posts = $q->all();
        }
    }

    public function run()
    {
        return $this->render('post_list',[
            'posts' => self::$posts,
            'type' => 'popular',
            'label' => $this->label,
            'ico' => 'ico-cat-hot'
        ]);
    }
}