<?php
namespace app\widgets;

use app\models\Banner;
use app\models\BannerDisplay;
use app\models\Category;
use app\models\Post;
use yii\base\Widget;
use yii\helpers\Html;

class BannersWidget extends Widget
{
    /* @var Banner[] */
    private $banners = [];
    public $position = null;
    public $attributes = [];

    public function init()
    {
        if(!empty($this->position)){

            $currentTime = date('Y-m-d H:i:s',time());

            /* @var $banners Banner[] */
            $banners = Banner::find()
                ->joinWith('bannerDisplays as bd')
                ->joinWith('bannerDisplays.place as place')
                ->where('bd.start_at < :cur',['cur' => $currentTime])
                ->andWhere('bd.end_at > :cur',['cur' => $currentTime])
                ->andWhere(['place.alias' => $this->position])
                ->all();

            $this->banners = $banners;
        }
    }

    public function run()
    {
        return !empty($this->banners) ? $this->render('banners',['banners' => $this->banners, 'attributes' => $this->attributes]) : null;
    }
}