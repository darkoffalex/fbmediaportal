<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\models\User;
use yii\web\JsExpression;
use kartik\select2\Select2;
use app\helpers\Constants;
use app\models\Category;
use kartik\dropdown\DropdownX;
use kartik\typeahead\Typeahead;
use yii\helpers\ArrayHelper;
use app\models\PostSources;

$this->title = Yii::t('admin',$model->isNewRecord ? 'Create post' : 'Update post');
$this->params['breadcrumbs'][] = ['label' => Yii::t('admin','Posts'), 'url' => Url::to(['/admin/posts/index'])];
$this->params['breadcrumbs'][] = $this->title;

/* @var $model \app\models\Post */
/* @var $languages \app\models\Language[] */
$languages = \app\models\Language::find()->all();
?>

<?php Yii::$app->view->registerCssFile('/js/imperavi-redactor/redactor.css'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/redactor.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/lang/ru.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/plugins/fontsize/fontsize.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/plugins/fontcolor/fontcolor.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/plugins/fullscreen/fullscreen.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/plugins/table/table.js'); ?>

<?php
$editorInit = "
    $('textarea.editor-area').redactor({
        minHeight : 180,
        maxHeight : 180,
        toolbarFixed : false,
        scroll : true,
        autoSize : false,
//        imageUpload: '".Url::to(['/site/upload'])."',
        plugins: ['fontsize','fontcolor','fullscreen','table'],
        lang : '".Yii::$app->language."'
    });";
Yii::$app->view->registerJs($editorInit,\yii\web\View::POS_END);
?>


<?php $form = ActiveForm::begin([
    'id' => 'edit-post-form',
    'options' => ['role' => 'form', 'method' => 'post', 'enctype' => 'multipart/form-data'],
    'enableClientValidation'=>false,
    'fieldConfig' => [
        'template' => "{label}\n{input}\n{error}\n",
        //'labelOptions' => ['class' => 'col-lg-1 control-label'],
    ],
]); ?>

<?php if(!$model->hasErrors() && Yii::$app->request->isPost): ?>
    <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i><?= Yii::t('admin','Saved'); ?></h4>
        <?= Yii::t('admin','All changes accepted'); ?>
    </div>
<?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <!-- Custom Tabs -->
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">

                    <?php foreach($languages as $index => $lng): ?>
                        <li class="<?= $index == 0 ? 'active' : '' ?>">
                            <a href="#tab_<?= $index; ?>" data-toggle="tab" aria-expanded="true"><?= $lng->self_name.' ('.$lng->prefix.')'; ?></a>
                        </li>
                    <?php endforeach; ?>

                    <li class="pull-right"><a href="#" class="text-muted"><i class="fa fa-globe"></i></a></li>
                </ul>
                <div class="tab-content">
                    <?php foreach($languages as $index => $lng): ?>
                        <div class="tab-pane <?= $index == 0 ? 'active' : '' ?>" id="tab_<?= $index; ?>">
                            <div class="form-group field-post_trl-name">
                                <label class="control-label" for="post_trl-name_<?= $lng->prefix; ?>"><?= Yii::t('admin','Name'); ?></label>
                                <input id="post_trl-name_<?= $lng->prefix; ?>" value="<?= $model->getATrl($lng->prefix)->name; ?>" class="form-control" name="Post[translations][<?= $lng->prefix; ?>][name]" type="text">
                            </div>

                            <div class="form-group field-post_trl-meta_small_text">
                                <label class="control-label" for="post_trl-meta_small_text_<?= $lng->prefix; ?>"><?= Yii::t('admin','Small text (excerpt)'); ?></label>
                                <textarea id="post_trl-meta_small_text_<?= $lng->prefix; ?>" class="form-control" name="Post[translations][<?= $lng->prefix; ?>][small_text]"><?= $model->getATrl($lng->prefix)->small_text; ?></textarea>
                            </div>

                            <div class="form-group field-post_trl-meta_keywords">
                                <label class="control-label" for="post_trl-full_text_<?= $lng->prefix; ?>"><?= Yii::t('admin','Full text'); ?></label>
                                <textarea id="post_trl-full_text_<?= $lng->prefix; ?>" class="form-control editor-area" name="Post[translations][<?= $lng->prefix; ?>][text]"><?= $model->getATrl($lng->prefix)->text; ?></textarea>
                            </div>
                        </div><!-- /.tab-pane -->
                    <?php endforeach; ?>
                </div><!-- /.tab-content -->
            </div><!-- nav-tabs-custom -->
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><?= Yii::t('admin','Basic configurations'); ?></h3></div>

                <div class="box-body">

                    <p>
                        <strong><?= Yii::t('admin','Type ID') ?></strong> :
                        <?php
                        $types = [
                            Constants::POST_TYPE_CREATED => Yii::t('admin','Created'),
                            Constants::POST_TYPE_IMPORTED => Yii::t('admin','Imported'),
                        ];
                        echo !empty($types[$model->type_id]) ? $types[$model->type_id] : Yii::t('admin','Unknown')
                        ?>
                    </p>

                    <p>
                        <strong><?= Yii::t('admin','Content Type ID') ?></strong> :
                        <?php
                        $types = [
                            Constants::CONTENT_TYPE_ARTICLE => Yii::t('admin','Article'),
                            Constants::CONTENT_TYPE_NEWS => Yii::t('admin','News'),
                            Constants::CONTENT_TYPE_PHOTO => Yii::t('admin','Photo'),
                            Constants::CONTENT_TYPE_VIDEO => Yii::t('admin','Video'),
                            Constants::CONTENT_TYPE_VOTING => Yii::t('admin','Voting'),
                        ];
                        echo !empty($types[$model->content_type_id]) ? $types[$model->content_type_id] : Yii::t('admin','Unknown')
                        ?>
                    </p>

                    <p>
                        <strong><?= Yii::t('admin','Comments') ?></strong> : 0
                    </p>

                    <hr>

                    <div class="form-group dropdown inactive-links">
                        <label class="control-label"><?= Yii::t('admin','Categories'); ?></label>

                        <div class="form-control categories-tags" data-toggle="dropdown">
                            <?php foreach($model->categories as $cat): ?>
                                <span class="label label-primary margin-r-5">
                                    <?= $cat->name; ?>
                                    <span class="fa fa-close icon-pointer" data-remove data-category-id="<?= $cat->id; ?>"></span>
                                    <input type="hidden" name="Post[categoriesChecked][]" value="<?= $cat->id; ?>">
                                </span>
                            <?php endforeach; ?>
                        </div>

                        <?php echo DropdownX::widget([
                            'items' => Category::buildRecursiveArrayForDropDown(),
                        ]);  ?>
                    </div>

                    <?= $form->field($model,'sticky_position_main')->dropDownList([
                        0 => Yii::t('admin','[NON STICKY]'),
                        1 => Yii::t('admin','On position {position_nr}',['position_nr' => 1]),
                        2 => Yii::t('admin','On position {position_nr}',['position_nr' => 2]),
                        3 => Yii::t('admin','On position {position_nr}',['position_nr' => 3]),
                        4 => Yii::t('admin','On position {position_nr}',['position_nr' => 4]),
                    ]); ?>

                    <?php foreach($model->postCategories as $pc): ?>
                        <div class="form-group field-post-sticky_position_main">
                            <label class="control-label" for="post-sticky_position_cat_<?= $pc->post_id.'_'.$pc->category_id; ?>"><?= Yii::t('admin','Sticky on page of category "{cat}"',['cat' => $pc->category->name]); ?></label>
                            <select id="post-sticky_position_cat_<?= $pc->post_id.'_'.$pc->category_id; ?>" class="form-control" name="Post[categoriesStickyPositions][<?= $pc->post_id.'_'.$pc->category_id; ?>]">
                                <option value="0"><?= Yii::t('admin','[NON STICKY]'); ?></option>
                                <?php for($i=1; $i <= 4; $i++): ?>
                                    <option <?php if($pc->sticky_position == $i): ?> selected <?php endif; ?> value="<?= $i; ?>"><?= Yii::t('admin','On position {position_nr}',['position_nr' => $i]); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>

                    <hr>

                    <?= $form->field($model, 'name')->textInput()->label(Yii::t('admin','Internal name')); ?>

                    <?= $form->field($model, 'status_id')->dropDownList([
                        Constants::STATUS_ENABLED => Yii::t('admin','Enabled'),
                        Constants::STATUS_DISABLED => Yii::t('admin','Disabled'),
                    ]); ?>

                    <?= $form->field($model, 'content_type_id')->dropDownList([
                        Constants::CONTENT_TYPE_ARTICLE => Yii::t('admin','Article'),
                        Constants::CONTENT_TYPE_NEWS => Yii::t('admin','News'),
                        Constants::CONTENT_TYPE_PHOTO => Yii::t('admin','Photo'),
                        Constants::CONTENT_TYPE_VIDEO => Yii::t('admin','Video'),
                        Constants::CONTENT_TYPE_VOTING => Yii::t('admin','Voting'),
                    ]); ?>

                    <hr>

                    <?= $form->field($model,'author_id')->widget(Select2::classname(), [
                        'initValueText' => !empty($model->author) ? $model->author->name.' '.$model->author->surname : '',
                        'options' => ['placeholder' => Yii::t('admin','Search for a user...')],
                        'language' => Yii::$app->language,
                        'theme' => Select2::THEME_DEFAULT,
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 2,
                            'language' => [
                                'errorLoading' => new JsExpression("function () { return '".Yii::t('admin','Waiting for results')."'; }"),
                            ],
                            'ajax' => [
                                'url' => Url::to(['/admin/users/ajax-search']),
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {q:params.term}; }')
                            ],
                            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                            'templateResult' => new JsExpression('function(user) { return user.text; }'),
                            'templateSelection' => new JsExpression('function (user) { return user.text; }'),
                        ],
                    ]) ?>

                    <?= $form->field($model,'author_custom_name')->textInput(); ?>

                    <?= $form->field($model,'source_url')->widget(Typeahead::className(),[
                        'defaultSuggestions' => array_values(ArrayHelper::map(PostSources::find()->orderBy('created_at DESC')->limit(10)->all(),'id','url')),
                        'pluginOptions' => ['highlight' => true],
                        'dataset' => [
                            [
                                'local' => array_values(([''] + ArrayHelper::map(PostSources::find()->orderBy('created_at DESC')->all(),'id','url'))),
                                'limit' => 20
                            ]
                        ]
                    ]); ?>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model,'video_key_yt')->textInput(); ?>
                            <?php if(!empty($model->video_key_yt)): ?>
                                <iframe width="300" src="https://www.youtube.com/embed/<?= $model->video_key_yt; ?>" frameborder="0" allowfullscreen></iframe>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model,'video_key_fb')->textInput(); ?>
                            <?php if(!empty($model->video_key_fb)): ?>
                                <iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Ffacebook%2Fvideos%2F<?= $model->video_key_fb; ?>%2F&width=300&show_text=false&appId=915460531914741&height=150" width="300" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
                            <?php endif; ?>
                        </div>
                    </div>

                    <label><?= Yii::t('admin','Images'); ?></label>
                    <table class="table table-hover ajax-reloadable" data-reload-url="<?= Url::to(['/admin/posts/list-images','id'=>$model->id]); ?>">
                        <tbody>
                        <tr>
                            <th><?= Yii::t('admin','Preview'); ?></th>
                            <th><?= Yii::t('admin','Status'); ?></th>
                            <th><?= Yii::t('admin','Actions'); ?></th>
                        </tr>
                        <?php if(!empty($model->postImages)): ?>
                            <?php foreach($model->postImages as $image): ?>
                                <tr>
                                    <td>
                                        <img height="200px" src="<?= $image->getFullUrl(); ?>">
                                    </td>
                                    <td>
                                        <?php if($image->status_id == Constants::STATUS_ENABLED): ?>
                                            <span class="label label-success"><?= Yii::t('admin','Enabled'); ?></span>
                                        <?php elseif($image->status_id == Constants::STATUS_DISABLED): ?>
                                            <span class="label label-danger"><?= Yii::t('admin','Disabled'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= Url::to(['/admin/posts/delete-image', 'id' => $image->id]); ?>" data-ajax-reloader=".ajax-reloadable" title="<?= Yii::t('admin','Delete'); ?>" aria-label="<?= Yii::t('admin','Delete'); ?>" data-confirm-ajax="<?= Yii::t('yii','Are you sure you want to delete this item?') ?>"><span class="glyphicon glyphicon-trash"></span></a>
                                        &nbsp;
                                        <a href="<?= Url::to(['/admin/posts/edit-image', 'id' => $image->id]); ?>" data-toggle="modal" data-target=".modal"><span class="glyphicon glyphicon-pencil"></span></a>
                                        &nbsp;
                                        <a href="<?= Url::to(['/admin/posts/move-image', 'id' => $image->id, 'dir' => 'up']); ?>" data-ajax-reloader=".ajax-reloadable"><span class="glyphicon glyphicon-arrow-up"></span></a>
                                        &nbsp;
                                        <a href="<?= Url::to(['/admin/posts/move-image', 'id' => $image->id, 'dir' => 'down']); ?>" data-ajax-reloader=".ajax-reloadable"><span class="glyphicon glyphicon-arrow-down"></span></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3"><?= Yii::t('admin','Post has no images'); ?></td>
                            </tr>
                        <?php endif; ?>

                        </tbody>
                    </table>
                    <a href="<?= Url::to(['/admin/posts/create-image','id'=>$model->id]); ?>" data-toggle="modal" data-target=".modal" class="btn btn-primary btn-xs pull-right"><?= Yii::t('admin','Add image'); ?></a>
                </div>

                <div class="box-footer">
                    <a class="btn btn-primary" href="<?php echo Url::to(['/admin/posts/index']); ?>"><?= Yii::t('admin','Back'); ?></a>
                    <button type="submit" class="btn btn-primary"><?= Yii::t('admin','Save') ?></button>
                </div>

            </div>
        </div>
    </div>

<?php ActiveForm::end(); ?>