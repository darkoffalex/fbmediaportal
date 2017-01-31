<?php
namespace app\widgets;

use app\models\Banner;
use app\models\BannerDisplay;
use app\models\Category;
use app\models\Post;
use yii\base\Widget;
use yii\helpers\Html;

class SearchFormWidget extends Widget
{
    public function init()
    {
    }

    public function run()
    {
        return $this->render('search_form');
    }
}