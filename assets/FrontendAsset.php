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
        'css/bootstrap.min.css',
        'css/font-awesome.min.css',
        'css/animate.css',
        'css/font.css',
        'css/li-scroller.css',
        'css/slick.css',
        'css/jquery.fancybox.css',
        'css/theme.css',
        'css/style.css',
    ];

    public $js = [
        'js/jquery.min.js',
        'js/wow.min.js',
        'js/bootstrap.min.js',
        'js/slick.min.js',
        'js/jquery.li-scroller.1.0.js',
        'js/jquery.newsTicker.min.js',
        'js/jquery.fancybox.pack.js',
        'js/custom.js',

//        'js/html5shiv.min.js',
//        'js/respond.min.js'
    ];
}
