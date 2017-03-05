<?php

use yii\db\Migration;

/**
 * Handles adding is_eternal to table `banner`.
 */
class m170304_193218_add_is_eternal_column_to_banner_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('banner', 'is_eternal', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('banner', 'is_eternal');
    }
}
