<?php use app\models\User; ?>
<?php /* @var $user User */ ?>
<?php $user = Yii::$app->user->identity; ?>

<aside class="main-sidebar">

    <section class="sidebar">


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
                        'label' => 'Выход',
                        'icon' => 'fa fa-sign-out',
                        'url' => ['/admin/main/logout']
                    ]
                ],
            ]
        ) ?>

    </section>

</aside>
