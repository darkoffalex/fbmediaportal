<?php

use yii\db\Migration;

/**
 * Handles adding crop to table `post_image`.
 */
class m170201_150438_add_crop_column_to_post_image_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post_image', 'need_crop', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post_image', 'need_crop');
    }
}
