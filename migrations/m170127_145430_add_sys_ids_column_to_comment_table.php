<?php

use yii\db\Migration;

/**
 * Handles adding sys_ids to table `comment`.
 */
class m170127_145430_add_sys_ids_column_to_comment_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('comment', 'adm_id', $this->integer());
        $this->addColumn('comment', 'answer_to_adm_id', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('comment', 'adm_id');
        $this->dropColumn('comment', 'answer_to_adm_id');
    }
}
