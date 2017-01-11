<?php

use yii\db\Migration;

/**
 * Handles adding link to table `banner`.
 */
class m170111_162747_add_link_column_to_banner_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('banner', 'link', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('banner', 'link');
    }
}
