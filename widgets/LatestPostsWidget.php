<?php
namespace app\widgets;

use app\helpers\Constants;
use app\models\Category;
use app\models\Post;
use yii\base\Widget;
use yii\db\Expression;
use yii\helpers\Html;
use Yii;

class LatestPostsWidget extends Widget
{
    /* @var Post */
    private static $posts = [];
    public $currentIds = [];
    public $siblingIds = [];
    public $limit = 7;
    public $label = '';

    public function init()
    {
        if(empty(self::$posts)){

            //if quantity of posts in current category too small and used siblings
            $siblingsUsed = false;

            //get all posts
            $q = Post::find();

            //select which's type is not forum
            $q->andWhere(new Expression('status_id = :status AND (kind_id != :kind OR kind_id IS NULL)', ['status' => Constants::STATUS_ENABLED, 'kind' => Constants::KIND_FORUM]));

            //if need filter by current category and it's children
            if(!empty($this->currentIds)){
                $q->joinWith('postCategories as pc');

                $countQ = clone $q;
                $countQ->andWhere(['pc.category_id' => $this->currentIds]);

                //if lack of posts
                if($countQ->count() < 7 && !empty($this->siblingIds)){
                    $siblingsUsed = true;
                    $q->andWhere(['pc.category_id' => $this->siblingIds]);
                }else{
                    $q->andWhere(['pc.category_id' => $this->currentIds]);
                }
            }

            //load translations
            $q->with(['trl']);

            //if used no siblings - regular sorting
            if(!$siblingsUsed){
               $q->orderBy('published_at DESC');
            //if used siblings - complex sorting (first should be current category's items)
            }else{
                $idsImploded = implode(',',$this->currentIds);
                $q->orderBy(new Expression("IF(pc.category_id IN ({$idsImploded}), 0, 2147483647) ASC, published_at DESC"));
            }

//            $q->distinct();
            $q->limit($this->limit);

            self::$posts = $q->all();
        }

    }

    public function run()
    {
        return $this->render('post_list',['posts' => self::$posts, 'type' => 'latest', 'label' => $this->label]);
    }
}