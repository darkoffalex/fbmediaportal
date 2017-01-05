<?php

use yii\db\Migration;

/**
 * Handles adding counters to table `user`.
 */
class m170104_214200_add_counters_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('user', 'counter_comments', $this->integer());
        $this->addColumn('user', 'counter_posts', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('user', 'counter_comments');
        $this->dropColumn('user', 'counter_posts');
    }
}
