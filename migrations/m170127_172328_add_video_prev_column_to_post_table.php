<?php

use yii\db\Migration;

/**
 * Handles adding video_prev to table `post`.
 */
class m170127_172328_add_video_prev_column_to_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post', 'video_preview_fb', $this->string());
        $this->addColumn('post', 'video_preview_yt', $this->string());
        $this->addColumn('post', 'video_attachment_id_fb',$this->string());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post', 'video_preview_fb');
        $this->dropColumn('post', 'video_preview_yt');
        $this->dropColumn('post', 'video_attachment_id_fb');
    }
}
