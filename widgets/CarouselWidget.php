<?php
namespace app\widgets;

use app\helpers\Constants;
use app\models\Banner;
use app\models\BannerDisplay;
use app\models\Category;
use app\models\Post;
use yii\base\Widget;
use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\Html;

class CarouselWidget extends Widget
{
    /* @var Post[] */
    private static $posts = [];
    public $currentIds = [];
    public $siblingIds = [];
    public $limit = 20;
    public $imgOnly = true;
    public $errorMessage = null;

    public function init()
    {
        if(empty(self::$posts)){
            try{
                $q = Post::find()
                    ->alias('p')
                    ->with(['trl','postImages.trl', 'author', 'comments'])
                    ->where(['status_id' => Constants::STATUS_ENABLED]);


                //if need filter by current category and it's children
                if(!empty($this->currentIds)){
                    $q->joinWith('postCategories as pc')->andWhere(['pc.category_id' => $this->currentIds]);
                    //if lack of posts
                    if($q->count() < 20 && !empty($this->siblingIds)){
                        $q->orWhere(['pc.category_id' => $this->siblingIds])
                            ->andWhere(['status_id' => Constants::STATUS_ENABLED]);
                    }
                }

                $q->andWhere(new Expression('kind_id != :kind OR kind_id IS NULL', ['kind' => Constants::KIND_FORUM]));
                $q->andWhere(new Expression('EXISTS (SELECT img.id FROM post_image img WHERE img.post_id = p.id)'));
                $q->andWhere(['content_type_id' => Constants::CONTENT_TYPE_POST]);

                $q->orderBy(new Expression('IF(sticky_position_main, sticky_position_main, 2147483647) ASC, IF(type_id = :lowestPriorityType, 2147483647, 0) ASC, published_at DESC',
                    ['lowestPriorityType' => Constants::CONTENT_TYPE_POST]
                ));

//                $q -> andWhere(new Expression('EXISTS (SELECT img.id FROM post_image img WHERE img.post_id = p.id)'));
//                $q -> andWhere(new Expression('p.id IN (SELECT img.post_id FROM post_image img)'));

                self::$posts = $q->offset(0)->limit($this->limit)->all();

            }catch (Exception $dbe){
                $this->errorMessage = nl2br($dbe);
            }
        }
    }

    public function run()
    {
        return $this->render('carousel',['posts' => self::$posts, 'limit' => $this->limit, 'error' => $this->errorMessage]);
    }
}