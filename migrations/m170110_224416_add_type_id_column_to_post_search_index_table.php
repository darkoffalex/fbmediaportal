<?php

use yii\db\Migration;

/**
 * Handles adding type_id to table `post_search_index`.
 */
class m170110_224416_add_type_id_column_to_post_search_index_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post_search_index', 'type_id', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post_search_index', 'type_id');
    }
}
