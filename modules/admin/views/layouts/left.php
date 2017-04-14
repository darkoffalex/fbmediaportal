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
                        'icon' => 'users',
                        'active' => $c == 'users',
                        'visible' => $user->role_id == \app\helpers\Constants::ROLE_ADMIN,
                        'url' => ['/admin/users/index'],
                    ],

                    [
                        'label' => Yii::t('admin','Stock'),
                        'icon' => 'cloud-download',
                        'active' => $c == 'stock',
                        'visible' => true,
                        'url' => ['/admin/stock/index'],
                    ],

                    [
                        'label' => Yii::t('admin','Categories'),
                        'icon' => 'folder',
                        'active' => $c == 'categories',
                        'visible' => true,
                        'url' => ['/admin/categories/index'],
                    ],

                    [
                        'label' => Yii::t('admin','Posts'),
                        'icon' => 'file',
                        'active' => $c == 'posts',
                        'visible' => true,
                        'url' => ['/admin/posts/index'],
                    ],

                    [
                        'label' => Yii::t('admin','Banners'),
                        'icon' => 'square-o',
                        'url' => [''],
                        'active' => $c == 'banner',
                        'visible' => true,
                        'items' => [
                            [
                                'label' => Yii::t('admin','List'),
                                'icon' => 'circle-o',
                                'url' => ['/admin/banner/index'],
                                'active' => $c == 'banner' && in_array($a,['index','create','update','delete']),
                                'visible' => true
                            ],
                            [
                                'label' => Yii::t('admin','Places'),
                                'icon' => 'circle-o',
                                'url' => ['/admin/banner/places'],
                                'active' => $c == 'banner' && in_array($a,['places','place-scheduler']),
                                'visible' => true
                            ]
                        ]
                    ],


                    ['label' => Yii::t('admin','Common config'), 'options' => ['class' => 'header']],

                    [
                        'label' => Yii::t('admin','Translations'),
                        'icon' => 'globe',
                        'url' => [''],
                        'active' => in_array($c,['languages','labels']),
                        'visible' => true,
                        'items' => [
                            [
                                'label' => Yii::t('admin','Languages'),
                                'icon' => 'circle-o',
                                'url' => ['/admin/languages/index'],
                                'active' => $c == 'languages',
                                'visible' => true
                            ],
                            [
                                'label' => Yii::t('admin','Labels'),
                                'icon' => 'circle-o',
                                'url' => ['/admin/labels/index'],
                                'active' => $c == 'labels',
                                'visible' => true
                            ]
                        ]
                    ],

                    [
                        'active' => $c == 'common-settings',
                        'label' => Yii::t('admin','Common settings'),
                        'icon' => 'gear',
                        'url' => ['/admin/common-settings/index']
                    ],

                    [
                        'label' => Yii::t('admin','Exit'),
                        'icon' => 'sign-out',
                        'url' => ['/admin/main/logout']
                    ]
                ],
            ]
        ) ?>

    </section>

</aside>
