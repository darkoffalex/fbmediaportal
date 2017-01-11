<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\models\User;
use app\helpers\Constants;

$this->title = Yii::t('admin',$model->isNewRecord ? 'Create banner' : 'Update banner');
$this->params['breadcrumbs'][] = ['label' => Yii::t('admin','Banner list'), 'url' => Url::to(['/admin/banner/index'])];
$this->params['breadcrumbs'][] = $this->title;

/* @var $model \app\models\Banner */
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><?= Yii::t('admin','Settings'); ?></h3></div>

            <?php $form = ActiveForm::begin([
                'id' => 'edit-banner-form',
                'options' => ['role' => 'form', 'method' => 'post', 'enctype' => 'multipart/form-data'],
                'enableClientValidation'=>false,
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}\n",
                    //'labelOptions' => ['class' => 'col-lg-1 control-label'],
                ],
            ]); ?>


            <div class="box-body">
                <?php if(!$model->hasErrors() && Yii::$app->request->isPost): ?>
                    <div class="alert alert-success alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check"></i><?= Yii::t('admin','Saved'); ?></h4>
                        <?= Yii::t('admin','All changes accepted'); ?>
                    </div>
                <?php endif; ?>

                <?= $form->field($model, 'name')->textInput(); ?>

                <?= $form->field($model, 'type_id')->dropDownList([
                    Constants::BANNER_TYPE_IMAGE => Yii::t('admin','Image'),
                    Constants::BANNER_TYPE_CODE => Yii::t('admin','Code'),
                ],['class' => 'form-control type-switcher']); ?>

                <div data-show-on=".type-switcher" data-show-on-val="<?= Constants::BANNER_TYPE_CODE; ?>">
                    <?= $form->field($model, 'code')->textarea(); ?>
                </div>

                <div data-show-on=".type-switcher" data-show-on-val="<?= Constants::BANNER_TYPE_IMAGE; ?>">
                    <?= $form->field($model, 'image')->fileInput(); ?>
                    <?= $form->field($model, 'link')->textInput(); ?>

                    <?php if($model->hasFile()): ?>
                        <img src="<?= $model->getImageUrl(); ?>" width="300" class="img-thumbnail">
                    <?php endif; ?>
                </div>
            </div>

            <div class="box-footer">
                <a class="btn btn-primary" href="<?php echo Url::to(['/admin/banner/index']); ?>"><?= Yii::t('admin','Back'); ?></a>
                <button type="submit" class="btn btn-primary"><?= Yii::t('admin','Save') ?></button>
            </div>

            <?php ActiveForm::end(); ?>
        </div>

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){

        var updateStates = function(){
            $('[data-show-on]').each(function(){
                var switcher = $($(this).data('show-on'));
                var value = $(this).data('show-on-val');

                if(switcher.val() != value){
                    $(this).addClass('hidden');
                }else{
                    $(this).removeClass('hidden');
                }
            });
        };

        $('.type-switcher').change(function(){
            updateStates();
        });

        updateStates();
    });
</script>