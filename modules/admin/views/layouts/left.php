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

//                    [
//                        'label' => 'Ссылка',
//                        'icon' => 'fa fa-file-text-o',
//                        'url' => [''],
//                        'active' => false,
//                        'visible' => false,
//                        'items' => [
//                            [
//                                'label' => 'Под-ссылка',
//                                'icon' => 'fa fa-circle-o',
//                                'url' => [''],
//                                'active' => false,
//                                'visible' => false
//                            ]
//                        ]
//                    ],

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
                        'label' => Yii::t('admin','Exit'),
                        'icon' => 'fa fa-sign-out',
                        'url' => ['/admin/main/logout']
                    ]
                ],
            ]
        ) ?>

    </section>

</aside>
