<?php

/* @var $this yii\web\View */
/* @var $controller \app\controllers\SiteController */
/* @var $user \yii\web\User */

$this->title = 'Главная страница сайта';
$controller = $this->context;
$user = Yii::$app->user->identity;
?>

<div class="site-index">

    <div class="jumbotron">
        <h1>Новостной FB портал</h1>
        <p class="lead">Проект в разработке</p>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4">
                <h2>Заголовок</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-default" href="#">Ссылка &raquo;</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Заголовок</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-default" href="#">Ссылка &raquo;</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Заголовок</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-default" href="#">Ссылка &raquo;</a></p>
            </div>
        </div>

    </div>
</div>
