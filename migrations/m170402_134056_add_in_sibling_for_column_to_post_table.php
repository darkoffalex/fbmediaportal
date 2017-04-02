<?php

use yii\db\Migration;

/**
 * Handles adding in_sibling_for to table `post`.
 */
class m170402_134056_add_in_sibling_for_column_to_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post', 'in_sibling_for_cats', $this->string());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post', 'in_sibling_for_cats');
    }
}
