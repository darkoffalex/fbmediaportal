<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `post_sources`.
 */
class m170110_162659_drop_post_sources_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropTable('post_sources');
    }

    /**
     * @inheritdoc
     */
    public function down()
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
}
