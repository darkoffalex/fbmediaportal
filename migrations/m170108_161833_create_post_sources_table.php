<?php

use yii\db\Migration;

/**
 * Handles the creation of table `post_sources`.
 */
class m170108_161833_create_post_sources_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('post_sources', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
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
        $this->dropTable('post_sources');
    }
}
