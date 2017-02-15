<?php

use yii\db\Migration;

/**
 * Handles adding stat to table `post`.
 */
class m170215_173259_add_stat_columns_to_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post', 'comment_count', $this->integer());
        $this->addColumn('post', 'about_turkey', $this->integer());
        $this->addColumn('post', 'last_comment_at', $this->dateTime());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post', 'comment_count');
        $this->dropColumn('post', 'about_turkey');
        $this->dropColumn('post', 'last_comment_at');
    }
}
