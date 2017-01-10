<?php

use yii\db\Migration;

/**
 * Handles the creation of table `post_group`.
 */
class m170110_163033_create_post_group_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('post_group', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
            'url' => $this->text(),
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
        $this->dropTable('post_group');
    }
}
