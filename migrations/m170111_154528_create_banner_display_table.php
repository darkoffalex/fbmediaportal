<?php

use yii\db\Migration;

/**
 * Handles the creation of table `banner_display`.
 * Has foreign keys to the tables:
 *
 * - `banner`
 * - `banner_place`
 */
class m170111_154528_create_banner_display_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('banner_display', [
            'id' => $this->primaryKey(),
            'banner_id' => $this->integer(),
            'place_id' => $this->integer(),
            'start_at' => $this->dateTime(),
            'end_at' => $this->dateTime(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by_id' => $this->integer(),
            'updated_by_id' => $this->integer(),
        ]);

        // creates index for column `banner_id`
        $this->createIndex(
            'idx-banner_display-banner_id',
            'banner_display',
            'banner_id'
        );

        // add foreign key for table `banner`
        $this->addForeignKey(
            'fk-banner_display-banner_id',
            'banner_display',
            'banner_id',
            'banner',
            'id',
            'CASCADE',
            'NO ACTION'
        );

        // creates index for column `place_id`
        $this->createIndex(
            'idx-banner_display-place_id',
            'banner_display',
            'place_id'
        );

        // add foreign key for table `banner_place`
        $this->addForeignKey(
            'fk-banner_display-place_id',
            'banner_display',
            'place_id',
            'banner_place',
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
        // drops foreign key for table `banner`
        $this->dropForeignKey(
            'fk-banner_display-banner_id',
            'banner_display'
        );

        // drops index for column `banner_id`
        $this->dropIndex(
            'idx-banner_display-banner_id',
            'banner_display'
        );

        // drops foreign key for table `banner_place`
        $this->dropForeignKey(
            'fk-banner_display-place_id',
            'banner_display'
        );

        // drops index for column `place_id`
        $this->dropIndex(
            'idx-banner_display-place_id',
            'banner_display'
        );

        $this->dropTable('banner_display');
    }
}
