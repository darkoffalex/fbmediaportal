<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;

/* @var $post \app\models\Post */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\PostsController */

$controller = $this->context;

/* @var $languages \app\models\Language[] */
$languages = \app\models\Language::find()->all();
?>

<tbody>
<tr>
    <th><?= Yii::t('admin','Answer'); ?></th>
    <th><?= Yii::t('admin','Votes'); ?></th>
    <th><?= Yii::t('admin','Percent'); ?></th>
    <th><?= Yii::t('admin','Actions'); ?></th>
</tr>

<?php if(!empty($post->postVoteAnswers)): ?>
    <?php foreach($post->postVoteAnswers as $answer): ?>
        <tr>
            <td><?= $answer->getATrl($languages[0]->prefix)->text; ?></td>
            <td><?= $answer->voted_qnt; ?></td>
            <td>
                <div class="progress progress-xs">
                    <div class="progress-bar progress-bar-danger" style="width: <?= $answer->getPercentage(); ?>%"></div>
                </div>
            </td>
            <td>
                <a href="<?= Url::to(['/admin/posts/delete-answer', 'id' => $answer->id]); ?>" data-ajax-reloader=".ajax-reloadable-answers" title="<?= Yii::t('admin','Delete'); ?>" aria-label="<?= Yii::t('admin','Delete'); ?>" data-confirm-ajax="<?= Yii::t('yii','Are you sure you want to delete this item?') ?>"><span class="glyphicon glyphicon-trash"></span></a>
                &nbsp;
                <a href="<?= Url::to(['/admin/posts/update-answer', 'id' => $answer->id]); ?>" data-toggle="modal" data-target=".modal"><span class="glyphicon glyphicon-pencil"></span></a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="4"><?= Yii::t('admin','No answers found'); ?></td>
    </tr>
<?php endif; ?>
</tbody>