<?php

use yii\db\Migration;

/**
 * Handles the creation of table `post_category_turkey`.
 */
class m170302_170140_create_post_category_turkey_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('post_category_turkey', [
            'post_id' => $this->integer(),
            'category_id' => $this->integer(),
            'sticky_position' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by_id' => $this->integer(),
            'updated_by_id' => $this->integer(),
            'PRIMARY KEY(post_id, category_id)',
        ]);

        // creates index for column `post_id`
        $this->createIndex(
            'idx-post_category_turkey-post_id',
            'post_category_turkey',
            'post_id'
        );

        // add foreign key for table `post`
        $this->addForeignKey(
            'fk-post_category_turkey-post_id',
            'post_category_turkey',
            'post_id',
            'post',
            'id',
            'CASCADE',
            'NO ACTION'
        );

        // creates index for column `category_id`
        $this->createIndex(
            'idx-post_category_turkey-category_id',
            'post_category_turkey',
            'category_id'
        );

        // add foreign key for table `category`
        $this->addForeignKey(
            'fk-post_category_turkey-category_id',
            'post_category_turkey',
            'category_id',
            'category',
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
        // drops foreign key for table `post`
        $this->dropForeignKey(
            'fk-post_category_turkey-post_id',
            'post_category_turkey'
        );

        // drops index for column `post_id`
        $this->dropIndex(
            'idx-post_category_turkey-post_id',
            'post_category_turkey'
        );

        // drops foreign key for table `category`
        $this->dropForeignKey(
            'fk-post_category_turkey-category_id',
            'post_category_turkey'
        );

        // drops index for column `category_id`
        $this->dropIndex(
            'idx-post_category_turkey-category_id',
            'post_category_turkey'
        );

        $this->dropTable('post_category_turkey');
    }
}
