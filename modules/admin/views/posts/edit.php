<?php

/* @var $model \app\models\Post */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use app\helpers\Constants;
use app\models\Category;
use kartik\dropdown\DropdownX;
use yii\helpers\ArrayHelper;
use app\models\PostGroup;
use app\helpers\Help;
use kartik\datetime\DateTimePicker;

$this->title = Yii::t('admin',$model->isNewRecord ? 'Create post' : 'Update post');
$this->params['breadcrumbs'][] = ['label' => Yii::t('admin','Posts'), 'url' => Url::to(['/admin/posts/index'])];
$this->params['breadcrumbs'][] = $this->title;

/* @var $model \app\models\Post */
/* @var $this \yii\web\View */

/* @var $languages \app\models\Language[] */
$languages = \app\models\Language::find()->orderBy('id ASC')->all();
?>

<?php Yii::$app->view->registerCssFile('/js/imperavi-redactor/redactor.css'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/redactor.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/lang/ru.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/plugins/fontsize/fontsize.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/plugins/fontcolor/fontcolor.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/plugins/fullscreen/fullscreen.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/plugins/table/table.js'); ?>
<?php //Yii::$app->view->registerJsFile('/js/moment/moment.js'); ?>

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
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><?= Yii::t('admin','Information'); ?></h3></div>
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
                            Constants::CONTENT_TYPE_POST => Yii::t('admin','Post')
                        ];
                        echo !empty($types[$model->content_type_id]) ? $types[$model->content_type_id] : Yii::t('admin','Unknown')
                        ?>
                    </p>

                    <p>
                        <strong><?= Yii::t('admin','Comments') ?></strong> : <?= count($model->comments); ?>
                        (<?= Html::a(Yii::t('admin','View'),['/admin/posts/comments', 'id' => $model->id],['data-toggle' => 'modal','data-target' => '.modal']); ?> |
                         <?= Html::a(Yii::t('admin','Refresh'),['/admin/posts/refresh', 'id' => $model->id],['data-confirm' => Yii::t('admin','Please DO NOT close this page until updating be finished!')]); ?>)
                    </p>

                    <p>
                        <?= Html::a(Yii::t('admin','Refresh commentator time-lines'),['/admin/posts/refresh-lines', 'id' => $model->id],['data-confirm' => Yii::t('admin','Please DO NOT close this page until updating be finished!')]); ?>
                    </p>

                    <p>
                        <?= Html::a(Yii::t('admin','Refresh attachments'),['/admin/posts/refresh-attachments', 'id' => $model->id],['data-confirm' => Yii::t('admin','Please DO NOT close this page until updating be finished!')]); ?>
                    </p>

                    <p>
                        <strong><?= Yii::t('admin','Created At') ?></strong> : <?= $model->created_at; ?>
                    </p>

                    <p>
                        <strong><?= Yii::t('admin','Published At') ?></strong> : <?= $model->published_at; ?>
                    </p>
                    <p>
                        <strong><?= Yii::t('admin','View on facebook') ?></strong> : <?= Html::a($model->getFbUrl(),$model->getFbUrl(),['target' => '_blank']); ?>
                    </p>
                    <p>
                        <strong><?= Yii::t('admin','View on portal') ?></strong> : <?= Html::a($model->getUrl(true,true),$model->getUrl(true,true),['target' => '_blank']); ?>
                    </p>
                    <p>
                        <?php $previewUrl = Url::to(['/main/post-preview', 'id' => $model->id],true); ?>
                        <strong><?= Yii::t('admin','Preview on portal') ?></strong> : <?= Html::a($previewUrl,$previewUrl,['target' => '_blank']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><?= Yii::t('admin','Settings'); ?></h3></div>
                <div class="box-body">

                    <?= $form->field($model, 'name')->textInput()->label(Yii::t('admin','Internal name')); ?>

                    <?= $form->field($model, 'content_type_id')->dropDownList([
                        Constants::CONTENT_TYPE_ARTICLE => Yii::t('admin','Article'),
                        Constants::CONTENT_TYPE_NEWS => Yii::t('admin','News'),
                        Constants::CONTENT_TYPE_PHOTO => Yii::t('admin','Photo'),
                        Constants::CONTENT_TYPE_VIDEO => Yii::t('admin','Video'),
                        Constants::CONTENT_TYPE_VOTING => Yii::t('admin','Voting'),
                        Constants::CONTENT_TYPE_POST => Yii::t('admin','Post')
                    ]); ?>

                    <?= $form->field($model, 'need_finish')->checkbox(); ?>

                    <?php $model->delayed_at = empty($model->delayed_at) ? $model->published_at : $model->delayed_at; ?>
                    <?= $form->field($model, 'delayed_at')->widget(DateTimePicker::className(),[
                        'type' => DateTimePicker::TYPE_INPUT,
                        'pluginOptions' => [
                            'autoclose'=>true,
                            'format' => 'yyyy-mm-dd hh:ii:ss'
                        ]
                    ]); ?>

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

                    <?= $form->field($model, 'status_id')->dropDownList([
                        Constants::STATUS_ENABLED => Yii::t('admin','Enabled'),
                        Constants::STATUS_DISABLED => Yii::t('admin','Disabled'),
                    ]); ?>

<!--                    --><?//= $form->field($model, 'published_at')->widget(DateTimePicker::className(),[
//                        'options' => ['placeholder' => 'Время публикации'],
//                        'convertFormat' => true,
//                        'pluginOptions' => [
//                            'format' => 'dd-M-yyyy hh:ii',
//                            'todayHighlight' => true
//                        ]
//                    ]); ?>

                    <ul class="nav nav-tabs">
                        <?php foreach($languages as $index => $lng): ?>
                            <li class="<?= $index == 0 ? 'active' : '' ?>">
                                <a href="#tab_<?= $index; ?>" data-toggle="tab" aria-expanded="true"><?= $lng->self_name.' ('.$lng->prefix.')'; ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="tab-content inner-block">
                        <?php foreach($languages as $index => $lng): ?>
                            <div class="tab-pane <?= $index == 0 ? 'active' : '' ?>" id="tab_<?= $index; ?>">

                                <div class="form-group field-post_trl-name">
                                    <label class="control-label" for="post_trl-name_<?= $lng->prefix; ?>"><?= Yii::t('admin','Name'); ?></label>
                                    <input id="post_trl-name_<?= $lng->prefix; ?>" value="<?= htmlentities($model->getATrl($lng->prefix)->name); ?>" class="form-control" name="Post[translations][<?= $lng->prefix; ?>][name]" type="text">
                                </div>

<!--                                --><?php //if(in_array($model->content_type_id,[Constants::CONTENT_TYPE_NEWS,Constants::CONTENT_TYPE_ARTICLE, Constants::CONTENT_TYPE_POST])): ?>
                                    <div class="form-group field-post_trl-meta_small_text">
                                        <label class="control-label" for="post_trl-meta_small_text_<?= $lng->prefix; ?>"><?= Yii::t('admin','Small text (excerpt)'); ?></label>
                                        <textarea id="post_trl-meta_small_text_<?= $lng->prefix; ?>" class="form-control" name="Post[translations][<?= $lng->prefix; ?>][small_text]"><?= htmlentities($model->getATrl($lng->prefix)->small_text); ?></textarea>
                                    </div>
<!--                                --><?php //endif; ?>

<!--                                --><?php //if(in_array($model->content_type_id,[Constants::CONTENT_TYPE_PHOTO,Constants::CONTENT_TYPE_ARTICLE,Constants::CONTENT_TYPE_NEWS,Constants::CONTENT_TYPE_VIDEO, Constants::CONTENT_TYPE_POST])): ?>
                                    <div class="form-group field-post_trl-text">
                                        <label class="control-label" for="post_trl-full_text_<?= $lng->prefix; ?>"><?= Yii::t('admin','Full text'); ?></label>
                                        <textarea id="post_trl-full_text_<?= $lng->prefix; ?>" class="form-control editor-area" name="Post[translations][<?= $lng->prefix; ?>][text]"><?= htmlentities($model->getATrl($lng->prefix)->text); ?></textarea>
                                    </div>
<!--                                --><?php //endif;?>

<!--                                --><?php //if($model->content_type_id == Constants::CONTENT_TYPE_VOTING): ?>
                                    <div class="form-group field-post_trl-question">
                                        <label class="control-label" for="post_trl-question_<?= $lng->prefix; ?>"><?= Yii::t('admin','Question'); ?></label>
                                        <textarea id="post_trl-question_<?= $lng->prefix; ?>" class="form-control" name="Post[translations][<?= $lng->prefix; ?>][question]"><?= htmlentities($model->getATrl($lng->prefix)->question); ?></textarea>
                                    </div>
<!--                                --><?php //endif; ?>

                            </div><!-- /.tab-pane -->
                        <?php endforeach; ?>
                    </div><!-- /.tab-content -->

                    <hr>

                    <?php $reload = Url::to(['/admin/posts/group-id-update']); ?>
                    <?php $link = Html::a(Yii::t('admin','Add new source'),['/admin/posts/create-group'],['data-toggle'=>'modal','data-target'=>'.modal']); ?>
                    <?php $data = ArrayHelper::merge(['' => Yii::t('admin','[NONE]')],ArrayHelper::map(PostGroup::find()->all(),'id','name')); ?>
                    <?= $form->field($model,'group_id',['template' => "{label}\n{input}\n{$link}\n{error}\n"])->dropDownList($data,['class' => 'form-control reload-ids', 'data-reload-url' => $reload]); ?>

                    <?= $form->field($model, 'kind_id')->dropDownList([
                        Constants::KIND_INTERESTING_CONTENT => Yii::t('admin','Useful content'),
                        Constants::KIND_INTERESTING_COMMENTS => Yii::t('admin','Interesting discussion'),
                        Constants::KIND_FORUM => Yii::t('admin','Forum')
                    ],['prompt' => '']); ?>


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

                    <?= $form->field($model,'author_id')->widget(Select2::classname(), [
                        'initValueText' => !empty($model->author) ? $model->author->name.' '.$model->author->surname : '',
                        'options' => ['placeholder' => Yii::t('admin','Search for a user...')],
                        'language' => Yii::$app->language,
                        'theme' => Select2::THEME_DEFAULT,
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 2,
                            'language' => [
                                'noResults' => new JsExpression("function () { return '".Yii::t('admin','No results found')."'; }"),
                                'searching' => new JsExpression("function () { return '".Yii::t('admin','Searching...')."'; }"),
                                'inputTooShort' => new JsExpression("function(args) {return '".Yii::t('admin','Type more characters')."'}"),
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

<!--                    --><?php //if(in_array($model->content_type_id,[Constants::CONTENT_TYPE_ARTICLE,Constants::CONTENT_TYPE_NEWS,Constants::CONTENT_TYPE_VIDEO, Constants::CONTENT_TYPE_POST])): ?>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model,'video_key_yt')->textInput(); ?>
                                <?php if(!empty($model->video_key_yt)): ?>
                                    <iframe width="300" src="<?= Help::youtubeurl($model->video_key_yt); ?>" frameborder="0" allowfullscreen></iframe>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model,'video_key_fb')->textInput(); ?>
                                <?php if(!empty($model->video_key_fb)): ?>
                                    <video width="300" controls>
                                        <source src="<?= $model->video_key_fb; ?>" type="video/mp4">
                                    </video>
<!--                                    <iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Ffacebook%2Fvideos%2F--><?//= '10211438588495727'; ?><!--%2F&width=300&show_text=false&appId=915460531914741&height=150" width="300" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>-->
<!--                                    <iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FIslam.Isgandarov%2Fvideos%2F10211438588495727%2F&width=360" width="360" height="640" style="border:none;overflow:hidden" scrolling=\"no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>-->
                                <?php endif; ?>
                            </div>
                        </div>
<!--                    --><?php //endif; ?>

<!--                    --><?php //if(in_array($model->content_type_id,[Constants::CONTENT_TYPE_ARTICLE,Constants::CONTENT_TYPE_NEWS,Constants::CONTENT_TYPE_PHOTO, Constants::CONTENT_TYPE_POST])): ?>
                        <hr>
                        <label><?= Yii::t('admin','Images'); ?></label>
                        <table class="table table-hover table-bordered ajax-reloadable" data-reload-url="<?= Url::to(['/admin/posts/list-images','id'=>$model->id]); ?>">
                            <?= $this->render('_images', ['post' => $model]); ?>
                        </table>
                        <br>
                        <a href="<?= Url::to(['/admin/posts/create-image','id'=>$model->id]); ?>" data-toggle="modal" data-target=".modal" class="btn btn-primary btn-xs pull-right"><?= Yii::t('admin','Add image'); ?></a>
                        <div style="clear: both;"></div>
<!--                    --><?php //endif; ?>

<!--                    --><?php //if($model->content_type_id == Constants::CONTENT_TYPE_VOTING): ?>
                        <hr>
                        <label><?= Yii::t('admin','Voting answers'); ?></label>
                        <table class="table table-hover table-bordered ajax-reloadable-answers" data-reload-url="<?= Url::to(['/admin/posts/list-answers','id'=>$model->id]); ?>">
                            <?= $this->render('_answers',['post' => $model]); ?>
                        </table>
                        <br>
                        <a href="<?= Url::to(['/admin/posts/update-answer','post_id' => $model->id]); ?>" data-toggle="modal" data-target=".modal" class="btn btn-primary btn-xs pull-right"><?= Yii::t('admin','Add answer'); ?></a>
                        <div style="clear: both;"></div>

                        <?= $form->field($model,'votes_only_authorized')->checkbox(); ?>

                        <?= $form->field($model,'stats_after_vote')->checkbox(); ?>
<!--                    --><?php //endif; ?>

                </div>

                <div class="box-footer">
                    <a class="btn btn-primary" href="<?php echo Url::to(['/admin/posts/index']); ?>"><?= Yii::t('admin','Back'); ?></a>
                    <button type="submit" class="btn btn-primary"><?= Yii::t('admin','Save') ?></button>
                </div>

            </div>
        </div>
    </div>

<?php ActiveForm::end(); ?>