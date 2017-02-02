<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;

/* @var $model \app\models\PostImage */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\PostsController */

$controller = $this->context;

/* @var $languages \app\models\Language[] */
$languages = \app\models\Language::find()->all();
?>

<?php $this->registerCssFile('@web/js/cropper/cropper.css'); ?>
<?php $this->registerJsFile('@web/js/cropper/cropper.js', ['position' => \yii\web\View::POS_HEAD]); ?>

    <div class="modal-header">
        <h4 class="modal-title"><?= Yii::t('admin','Image crop settings'); ?></h4>
    </div>

<?php $form = ActiveForm::begin([
    'id' => 'crop-image-form',
    'options' => ['role' => 'form', 'method' => 'post', 'enctype' => 'multipart/form-data'],
    'enableClientValidation'=>false,
    'fieldConfig' => [
        'template' => "{label}\n{input}\n{error}\n",
    ],
]); ?>

    <div class="modal-body">
        <?= $form->field($model, 'crop_settings')->hiddenInput(['class' => 'crop-data-field'])->label(false); ?>
        <?= $form->field($model, 'strict_ratio')->checkbox(['class' => 'strict-ration-check']); ?>

        <div class="row">
            <img class="img-responsive" id="crop-image" style="width: 100%;" src="<?= $model->getFullUrl(); ?>">
        </div>

        <script type="text/javascript">
            var cropper = null;

            var reInitCrop = function(aspectRatio){

                if(cropper != null){
                    cropper.destroy();
                }

                var image = document.getElementById('crop-image');
                cropper = new Cropper(image, {
                    aspectRatio: aspectRatio,
                    viewMode: 2,
                    scalable: false,
                    rotatable: false,
                    zoomable: false,
                    zoomOnWheel: false,
                    crop: function(e) {

                        var cropData = {};
                        cropData.x = e.detail.x;
                        cropData.y = e.detail.y;
                        cropData.w = e.detail.width;
                        cropData.h = e.detail.height;

                        $('.crop-data-field').val(JSON.stringify(cropData));
                    }
                });
            };

            setTimeout(function(){
                if($('.strict-ration-check').prop('checked')){
                    reInitCrop(706/311);
                }else{
                    reInitCrop(NaN);
                }
            },50);

            $('.strict-ration-check').change(function(){
                if($(this).prop('checked')){
                    reInitCrop(706/311);
                }else{
                    reInitCrop(NaN);
                }
            });


        </script>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Yii::t('admin','Close'); ?></button>
        <button type="button" class="btn btn-primary submit-ajax-btn" data-ajax-form="#crop-image-form" data-ok-reload=".ajax-reloadable"><?= Yii::t('admin','Save') ?></button>
    </div>

<?php ActiveForm::end(); ?>
