<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $jsOptions = ['position' => View::POS_HEAD];

    public $css = [
        'css/site.css',
        'css/common.css',
        'https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css'
    ];

    public $js = [
        'js/common.js',
        'https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js'
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
