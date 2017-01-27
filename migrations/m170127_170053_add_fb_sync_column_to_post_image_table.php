<?php

use yii\db\Migration;

/**
 * Handles adding fb_sync to table `post_image`.
 */
class m170127_170053_add_fb_sync_column_to_post_image_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post_image', 'fb_sync_id', $this->string());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post_image', 'fb_sync_id');
    }
}
