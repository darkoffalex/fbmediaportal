<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\MainController */
/* @var $content string */

app\assets\AppAsset::register($this);
dmstr\web\AdminLteAsset::register($this);

$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
$controller = $this->context;
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <!-- sidebar-collapse-->
    <body class="hold-transition <?= \dmstr\helpers\AdminLteHelper::skinClass(); ?> sidebar-mini sidebar-collapse">
    <?php $this->beginBody() ?>

    <div class="wrapper">
        <?= $this->render('header',['directoryAsset' => $directoryAsset]); ?>
        <?= $this->render('left',['directoryAsset' => $directoryAsset]);?>
        <?= $this->render('content',['content' => $content, 'directoryAsset' => $directoryAsset]);?>
    </div>

    <?php $this->endBody() ?>

    <div class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $('.modal').on('hide.bs.modal', function() {
            $(this).removeData();
            $(this).find('.modal-content').html('');
        });
    </script>

    </body>
    </html>
<?php $this->endPage() ?>