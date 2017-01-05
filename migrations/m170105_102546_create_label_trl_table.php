<?php

use yii\db\Migration;

/**
 * Handles the creation of table `label_trl`.
 * Has foreign keys to the tables:
 *
 * - `label`
 */
class m170105_102546_create_label_trl_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('label_trl', [
            'id' => $this->primaryKey(),
            'label_id' => $this->integer(),
            'lng' => $this->string(5),
            'word' => $this->text(),
        ]);

        // creates index for column `label_id`
        $this->createIndex(
            'idx-label_trl-label_id',
            'label_trl',
            'label_id'
        );

        // add foreign key for table `label`
        $this->addForeignKey(
            'fk-label_trl-label_id',
            'label_trl',
            'label_id',
            'label',
            'id',
            'CASCADE',
            'NO ACTION'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `label`
        $this->dropForeignKey(
            'fk-label_trl-label_id',
            'label_trl'
        );

        // drops index for column `label_id`
        $this->dropIndex(
            'idx-label_trl-label_id',
            'label_trl'
        );

        $this->dropTable('label_trl');
    }
}
