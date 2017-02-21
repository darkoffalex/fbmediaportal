<?php

use yii\db\Migration;

/**
 * Handles adding search_and_status to table `post`.
 */
class m170221_001747_add_search_and_status_column_to_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post', 'search_keywords', $this->text());
        $this->addColumn('post', 'need_update', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post', 'search_keywords');
        $this->dropColumn('post', 'need_update');
    }
}
