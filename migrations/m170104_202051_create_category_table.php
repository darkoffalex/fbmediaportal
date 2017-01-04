<?php

use yii\db\Migration;

/**
 * Handles the creation of table `category`.
 */
class m170104_202051_create_category_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('category', [
            'id' => $this->primaryKey(),
            'parent_category_id' => $this->integer(),
            'name' => $this->string(),
            'status_id' => $this->integer(),
            'priority' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by_id' => $this->integer(),
            'updated_by_id' => $this->integer(),
            'icon_file' => $this->string(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('category');
    }
}
