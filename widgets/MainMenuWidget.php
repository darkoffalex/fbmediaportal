<?php
namespace app\widgets;

use app\helpers\Constants;
use app\models\Category;
use yii\base\Widget;
use yii\helpers\Html;
use Yii;

class MainMenuWidget extends Widget
{
    public $root = 0;
    private $activeId = null;
    private static $categories = [];

    public function init()
    {
        if(Yii::$app->controller->id == 'category'){
            $this->activeId = Yii::$app->request->get('id',null);
        }

        if(empty(self::$categories)){
            self::$categories = Category::find()
                ->where(['parent_category_id' => (int)$this->root, 'status_id' => Constants::STATUS_ENABLED])
                ->with([
                    'trl',
                    'children.trl',
//                    'parent.trl',
//                    'parent.children.trl',
                    'children.parent',
//                    'children.parent.trl',
                    'children.children.trl',
                    'children.children.parent',
                ])
                ->orderBy('priority ASC')
                ->all();
        }
    }

    public function run()
    {
        return $this->render('main_menu',['categories' => self::$categories, 'active' => $this->activeId]);
    }
}