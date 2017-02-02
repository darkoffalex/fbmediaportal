<?php

use yii\db\Migration;

/**
 * Handles adding crop_param to table `post_image`.
 */
class m170201_173124_add_crop_param_column_to_post_image_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post_image', 'crop_settings', $this->string());
        $this->addColumn('post_image', 'strict_ratio', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post_image', 'crop_settings');
        $this->dropColumn('post_image', 'strict_ratio');
    }
}
