<?php

use yii\db\Migration;

/**
 * Handles the creation of table `label`.
 */
class m170105_101908_create_label_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('label', [
            'id' => $this->primaryKey(),
            'source_word' => $this->text()->notNull(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by_id' => $this->integer(),
            'updated_by_id' => $this->integer(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('label');
    }
}
