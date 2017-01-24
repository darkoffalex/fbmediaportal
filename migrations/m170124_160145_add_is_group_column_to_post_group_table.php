<?php

use yii\db\Migration;

/**
 * Handles adding is_group to table `post_group`.
 */
class m170124_160145_add_is_group_column_to_post_group_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post_group', 'is_group', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post_group', 'is_group');
    }
}
