<?php

use yii\db\Migration;

/**
 * Handles the creation of table `common_settings`.
 */
class m170111_231716_create_common_settings_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('common_settings', [
            'id' => $this->primaryKey(),
            'header_logo_filename' => $this->string(),
            'footer_content' => $this->text(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('common_settings');
    }
}
