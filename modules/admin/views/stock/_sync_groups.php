<?php
use yii\helpers\Url;
use kartik\switchinput\SwitchInput;

/* @var $groups \app\models\PostGroup[] */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\StockController */

$controller = $this->context;
Yii::$app->view->registerJsFile('/js/nested-multi-sel.js');
?>

<div class="modal-header">
    <h4 class="modal-title"><?= Yii::t('admin','Source-gropus settings'); ?></h4>
</div>

<div id="modal-groups-body" class="modal-body" style="max-height: 500px; overflow-y: scroll;">
    <table class="table table-hover">
        <?php if(!empty($groups)): ?>
            <tbody>
            <tr>
                <th><?= Yii::t('admin','Name'); ?></th>
                <th><?= Yii::t('admin','Stock'); ?></th>
                <th><?= Yii::t('admin','Status'); ?></th>
            </tr>
            <?php foreach ($groups as $group): ?>
                <tr>
                    <td><?= $group->name; ?></td>
                    <td>
                        <input <?= !$group->stock_sync ? 'disabled' : ''; ?> data-update-url="<?= Url::to(['/admin/stock/group-status','id' => $group->id]); ?>" <?= $group->stock_enabled && $group->stock_sync ? 'checked' : ''; ?> class="toggle-swicthes" type="checkbox">
                    </td>
                    <td>
                        <?php if($group->stock_sync): ?>
                            <span class="label label-success"><?= Yii::t('admin','Synchronized'); ?></span>
                        <?php else: ?>
                            <span class="label label-danger"><?= Yii::t('admin','Not synchronized'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        <?php else: ?>
            <tr>
                <td><?= Yii::t('admin','Nothing found'); ?></td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Yii::t('admin','Close'); ?></button>
    <a data-ajax-reloader="#modal-groups-body" data-load-parent="yes" href="<?= Url::to(['/admin/stock/sync-groups']); ?>" class="btn btn-primary"><?= Yii::t('admin','Synchronize (refresh)') ?></a>
</div>

<script type="text/javascript">
    $(function() {
        $('.toggle-swicthes').bootstrapToggle({
            on: '<?= Yii::t('admin','Yes'); ?>',
            off: '<?= Yii::t('admin','No'); ?>'
        }).change(function () {
            var status = $(this).prop('checked');
            var updateUrl = $(this).data('update-url');

            $.ajax({
                url: updateUrl + '?status=' + (status == true ? '1' : '0'),
                type: 'GET',
                async: false,
                success: function(reloaded_data){}
            });
        });
    })
</script>