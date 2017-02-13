<?php

use yii\db\Migration;

/**
 * Handles adding need_finish to table `post`.
 */
class m170208_211816_add_need_finish_column_to_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post', 'need_finish', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post', 'need_finish');
    }
}
