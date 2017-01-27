<?php
namespace app\widgets;

use app\helpers\Constants;
use app\models\Category;
use yii\base\Widget;
use yii\helpers\Html;

class MainMenuWidget extends Widget
{
    public $root = 0;
    private $categories = [];

    public function init()
    {
        $this->categories = Category::find()
            ->where(['parent_category_id' => (int)$this->root, 'status_id' => Constants::STATUS_ENABLED])
            ->with('trl')
            ->with('children')
            ->with('children.trl')
            ->orderBy('priority ASC')
            ->all();
    }

    public function run()
    {
        return $this->render('main_menu',['categories' => $this->categories]);
    }
}