<?php

use yii\db\Migration;

/**
 * Handles adding answer_fb to table `comment`.
 */
class m170123_175716_add_answer_fb_column_to_comment_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('comment', 'answer_to_fb_id', $this->string());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('comment', 'answer_to_fb_id');
    }
}
