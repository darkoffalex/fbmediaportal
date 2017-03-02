<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'FB Media Portal',
    'name' => 'FB Media Portal',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log','thumbnail'],
    'language' => 'ru',

    'modules' => [
        'gridview' =>  'kartik\grid\Module',
        'admin' => ['class' => 'app\modules\admin\AdminModule'],
    ],

    'components' => [

        'i18n' => [
            'translations' => [
                'admin' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en-US',
                ],
            ],
        ],

        'thumbnail' => [
            'class' => 'himiklab\thumbnail\EasyThumbnail',
            'cacheAlias' => 'assets/thumbnails',
        ],

        'assetManager' => [
            'bundles' => [
                'dmstr\web\AdminLteAsset' => [
                    'skin' => 'skin-blue',
                ],
            ],
        ],

        'request' => [
            'cookieValidationKey' => 'Inv98aJIqVcdG-5g34NaHHMvOdbD3Z9q',
            'baseUrl' => '',
        ],

        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],

        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],

        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.yandex.ru',
                'username' => 'message@calltoclient.com',
                'password' => 'rV68gE6D4fw5s4X5zae445',
                'port' => '465',
                'encryption' => 'ssl',
            ]
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),

        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'admin' => 'admin/main/index',
                'admin/<controller>' => 'admin/<controller>/index',
                'admin/<controller>/<action>/<id:\d+>' => 'admin/<controller>/<action>',
                'admin/<controller>/<action>' => 'admin/<controller>/<action>',

                'search/<q:\w+(-\w+)*>' => 'main/search',
                'search' => 'main/search',

                '/' => 'main/index',
                '<controller>' => '<controller>/index',

                'post/<id:\d+>/<title:\w+(-\w+)*>' => 'main/post',
                'category/<id:\d+>/<title:\w+(-\w+)*>' => 'main/category',
                'pages/<type:\w+(-\w+)*>' => 'main/pages',

                '<controller>/<action>/<id:\d+>/<title:\w+(-\w+)*>' => '<controller>/<action>',
                '<controller>/<action>/<id:\d+>/<status:\d+>' => '<controller>/<action>',
                '<controller>/<action>/<id:\d+>' => '<controller>/<action>',
                '<controller>/<action>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/*' => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '78.56.14.109']
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
