<?php

use yii\db\Migration;

/**
 * Handles adding stock_enabled to table `post_group`.
 */
class m170221_021639_add_stock_enabled_column_to_post_group_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post_group', 'stock_enabled', $this->integer());
        $this->addColumn('post_group', 'stock_sync', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post_group', 'stock_enabled');
        $this->dropColumn('post_group', 'stock_sync');
    }
}
