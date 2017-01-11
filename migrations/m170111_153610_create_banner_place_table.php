<?php

use yii\db\Migration;

/**
 * Handles the creation of table `banner_place`.
 */
class m170111_153610_create_banner_place_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('banner_place', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'alias' => $this->string()->notNull()->unique(),
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
        $this->dropTable('banner_place');
    }
}
