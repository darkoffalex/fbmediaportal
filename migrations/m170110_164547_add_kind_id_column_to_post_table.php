<?php

use yii\db\Migration;

/**
 * Handles adding kind_id to table `post`.
 */
class m170110_164547_add_kind_id_column_to_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post', 'kind_id', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post', 'kind_id');
    }
}
