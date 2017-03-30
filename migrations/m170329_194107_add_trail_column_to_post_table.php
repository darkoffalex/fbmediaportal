<?php

use yii\db\Migration;

/**
 * Handles adding trail to table `post`.
 */
class m170329_194107_add_trail_column_to_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post', 'trail', $this->string());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post', 'trail');
    }
}
