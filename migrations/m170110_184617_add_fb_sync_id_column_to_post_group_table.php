<?php

use yii\db\Migration;

/**
 * Handles adding fb_sync_id to table `post_group`.
 */
class m170110_184617_add_fb_sync_id_column_to_post_group_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post_group', 'fb_sync_id', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post_group', 'fb_sync_id');
    }
}
