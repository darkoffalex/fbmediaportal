<?php

use yii\db\Migration;

/**
 * Handles the creation of table `banner`.
 */
class m170111_153159_create_banner_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('banner', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'type_id' => $this->integer(),
            'code' => $this->text(),
            'custom_html' => $this->text(),
            'image_filename' => $this->string(),
            'clicks' => $this->integer(),
            'created_by_id' => $this->integer(),
            'updated_by_id' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('banner');
    }
}
