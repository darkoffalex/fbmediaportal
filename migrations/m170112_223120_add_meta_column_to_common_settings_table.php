<?php

use yii\db\Migration;

/**
 * Handles adding meta to table `common_settings`.
 */
class m170112_223120_add_meta_column_to_common_settings_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('common_settings', 'meta_keywords', $this->text());
        $this->addColumn('common_settings', 'meta_description', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('common_settings', 'meta_keywords');
        $this->dropColumn('common_settings', 'meta_description');
    }
}
