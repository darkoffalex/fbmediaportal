<?php

use yii\db\Migration;

/**
 * Handles adding delayed to table `post`.
 */
class m170406_132416_add_delayed_column_to_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post', 'delayed_at', $this->dateTime());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post', 'delayed_at');
    }
}
