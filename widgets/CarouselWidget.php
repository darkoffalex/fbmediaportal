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
    public $filtration = [Constants::KIND_INTERESTING_COMMENTS,Constants::KIND_INTERESTING_CONTENT];
    public $limit = 15;
    public $imgOnly = true;
    public $errorMessage = null;

    public function init()
    {
        if(empty(self::$posts)){
            try{
                $q = Post::find()
                    ->alias('p')
                    ->where(['status_id' => Constants::STATUS_ENABLED])
                    ->with(['trl','postImages']);

                if(!empty($this->filtration)){
                    $q -> andWhere(['kind_id' => $this->filtration]);
                }

                if($this->imgOnly){
                    $q -> andWhere(new Expression('EXISTS (SELECT img.id FROM post_image img WHERE img.post_id = p.id)'));
//                $q -> andWhere(new Expression('p.id IN (SELECT img.post_id FROM post_image img)'));
                }

                self::$posts = $q->orderBy('published_at DESC')
                    ->offset(0)
                    ->limit($this->limit)
                    ->all();

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