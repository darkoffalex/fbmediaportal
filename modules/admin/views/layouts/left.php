<?php use app\models\User; ?>
<?php /* @var $user User */ ?>
<?php $user = Yii::$app->user->identity; ?>

<aside class="main-sidebar">

    <section class="sidebar">

        <?php $c = Yii::$app->controller->id; ?>
        <?php $a = Yii::$app->controller->action->id; ?>

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [

                    ['label' => Yii::t('admin','Basic'), 'options' => ['class' => 'header']],
                    [
                        'label' => Yii::t('admin','Users'),
                        'icon' => 'fa fa-users',
                        'active' => $c == 'users',
                        'visible' => $user->role_id == \app\helpers\Constants::ROLE_ADMIN,
                        'url' => ['/admin/users/index'],
                    ],

                    [
                        'label' => Yii::t('admin','Categories'),
                        'icon' => 'fa fa-folder',
                        'active' => $c == 'categories',
                        'visible' => true,
                        'url' => ['/admin/categories/index'],
                    ],

                    [
                        'label' => Yii::t('admin','Posts'),
                        'icon' => 'fa fa-file',
                        'active' => $c == 'posts',
                        'visible' => true,
                        'url' => ['/admin/posts/index'],
                    ],

                    [
                        'label' => Yii::t('admin','Banners'),
                        'icon' => 'fa fa-square-o',
                        'url' => [''],
                        'active' => $c == 'banners',
                        'visible' => true,
                        'items' => [
                            [
                                'label' => Yii::t('admin','List'),
                                'icon' => 'fa fa-circle-o',
                                'url' => ['/admin/banners/index'],
                                'active' => $c == 'banners' && in_array($a,['index','create','update','delete']),
                                'visible' => true
                            ],
                            [
                                'label' => Yii::t('admin','Places'),
                                'icon' => 'fa fa-circle-o',
                                'url' => ['/admin/banners/places'],
                                'active' => $c == 'banners' && in_array($a,['places']),
                                'visible' => true
                            ]
                        ]
                    ],


                    ['label' => Yii::t('admin','Common config'), 'options' => ['class' => 'header']],

                    [
                        'label' => Yii::t('admin','Translations'),
                        'icon' => 'fa fa-globe',
                        'url' => [''],
                        'active' => in_array($c,['languages','labels']),
                        'visible' => true,
                        'items' => [
                            [
                                'label' => Yii::t('admin','Languages'),
                                'icon' => 'fa fa-circle-o',
                                'url' => ['/admin/languages/index'],
                                'active' => $c == 'languages',
                                'visible' => true
                            ],
                            [
                                'label' => Yii::t('admin','Labels'),
                                'icon' => 'fa fa-circle-o',
                                'url' => ['/admin/labels/index'],
                                'active' => $c == 'labels',
                                'visible' => true
                            ]
                        ]
                    ],

                    [
                        'label' => Yii::t('admin','Exit'),
                        'icon' => 'fa fa-sign-out',
                        'url' => ['/admin/main/logout']
                    ]
                ],
            ]
        ) ?>

    </section>

</aside>
