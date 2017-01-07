<?php

use yii\bootstrap\Html;
use yii\helpers\Url;
use app\helpers\Constants;
use app\helpers\Help;

/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\CategoriesController */
/* @var $user \app\models\User */
/* @var $categories \app\models\Category[] */

$controller = $this->context;
$user = Yii::$app->user->identity;

$this->title = Yii::t('admin','Category list');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><?= Yii::t('admin','List'); ?></h3>

<!--                <div class="box-tools">-->
<!--                    <div class="input-group" style="width: 150px;">-->
<!--                        <input name="table_search" class="form-control input-sm pull-right" placeholder="Search" type="text">-->
<!--                        <div class="input-group-btn">-->
<!--                            <button class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->

            </div><!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>

                    <?php if(!empty($categories)): ?>
                        <tr>
                            <th><?= Yii::t('admin','ID'); ?></th>
                            <th><?= Yii::t('admin','Name'); ?></th>
                            <th><?= Yii::t('admin','Items'); ?></th>
                            <th><?= Yii::t('admin','Created'); ?></th>
                            <th><?= Yii::t('admin','Updated'); ?></th>
                            <th><?= Yii::t('admin','Status'); ?></th>
                            <th><?= Yii::t('admin','Actions'); ?></th>
                        </tr>

                        <?php foreach($categories as $category): ?>
                            <tr>
                                <td><?= $category->id; ?></td>
                                <td>
                                    <?php for($i=1; $i < $category->getDepth(); $i++): ?> - <?php endfor; ?>
                                    <?php if($category->getDepth() == 1): ?>
                                        <strong><?= $category->name; ?></strong>
                                    <?php else: ?>
                                        <?= $category->name; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= 0; ?></td>
                                <td>
                                    <a href="<?= Url::to(['/admin/users/preview', 'id' => $category->createdBy->id]); ?>" data-toggle="modal" data-target=".modal"><?= $category->createdBy->name.' '.$category->createdBy->surname; ?></a><br>
                                </td>
                                <td>
                                    <a href="<?= Url::to(['/admin/users/preview', 'id' => $category->updatedBy->id]); ?>" data-toggle="modal" data-target=".modal"><?= $category->updatedBy->name.' '.$category->updatedBy->surname; ?></a><br>
                                </td>
                                <td>
                                    <?php if($category->status_id == Constants::STATUS_ENABLED): ?>
                                        <span class="label label-success"><?= Yii::t('admin','Enabled'); ?></span>
                                    <?php elseif($category->status_id == Constants::STATUS_DISABLED): ?>
                                        <span class="label label-danger"><?= Yii::t('admin','Disabled'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= Url::to(['/admin/categories/delete', 'id' => $category->id]); ?>" title="<?= Yii::t('admin','Delete'); ?>" aria-label="<?= Yii::t('admin','Delete'); ?>" data-confirm="<?= Yii::t('yii','Are you sure you want to delete this item?') ?>"><span class="glyphicon glyphicon-trash"></span></a>
                                    &nbsp;
                                    <a href="<?= Url::to(['/admin/categories/edit', 'id' => $category->id]); ?>"><span class="glyphicon glyphicon-pencil"></span></a>
                                    &nbsp;
                                    <a href="<?= Url::to(['/admin/categories/move', 'id' => $category->id, 'dir' => 'up']); ?>"><span class="glyphicon glyphicon-arrow-up"></span></a>
                                    &nbsp;
                                    <a href="<?= Url::to(['/admin/categories/move', 'id' => $category->id, 'dir' => 'down']); ?>"><span class="glyphicon glyphicon-arrow-down"></span></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <th><?= Yii::t('admin','You have no categories'); ?></th>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div><!-- /.box-body -->
            <div class="box-footer">
                <a data-toggle="modal" data-target=".modal" href="<?php echo Url::to(['/admin/categories/create']); ?>" class="btn btn-primary btn-sm"><?= Yii::t('admin','Create'); ?></a>
            </div>
        </div><!-- /.box -->
    </div>
</div>
