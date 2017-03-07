<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Class FrontendAsset
 * @package app\assets
 */
class FrontendAsset extends AssetBundle
{
    public $basePath = '@webroot/frontend';
    public $baseUrl = '@web/frontend';
    public $jsOptions = ['position' => View::POS_HEAD];

    public $css = [
        'css/styles.min.css',
        'css/common.css'
    ];

    public $js = [
        'js/scripts.min.js',
        'js/common.js',
        'js/afterglow.min.js'
    ];
}
