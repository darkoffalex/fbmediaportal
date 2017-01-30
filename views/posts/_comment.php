<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;
use kartik\dropdown\DropdownX;
use kartik\select2\Select2;
use yii\web\JsExpression;

/* @var $model \app\models\Comment */
/* @var $comment \app\models\Comment */
/* @var $post \app\models\Post */
/* @var $this \yii\web\View */
/* @var $controller \app\controllers\PostsController */

$controller = $this->context;
?>

    <div class="modal-header">
        <h4 class="modal-title">Оставить коментарий</h4>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'create-post-form',
        'options' => ['role' => 'form', 'method' => 'post', 'enctype' => 'multipart/form-data'],
        'enableClientValidation'=>false,
        'enableAjaxValidation' => true,
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{error}\n",
            //'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

    <div class="modal-body">
        <?= $form->field($model, 'text')->textarea()->label('Текст вашего коментария'); ?>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Закрыть</button>
        <button type="submit" class="btn btn-primary">Отправить</button>
    </div>

<?php ActiveForm::end(); ?>