<?php

use yii\db\Migration;

/**
 * Handles the creation of table `post_category`.
 * Has foreign keys to the tables:
 *
 * - `post`
 * - `category`
 */
class m170105_185825_create_junction_post_and_category_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('post_category', [
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
            'idx-post_category-post_id',
            'post_category',
            'post_id'
        );

        // add foreign key for table `post`
        $this->addForeignKey(
            'fk-post_category-post_id',
            'post_category',
            'post_id',
            'post',
            'id',
            'CASCADE',
            'NO ACTION'
        );

        // creates index for column `category_id`
        $this->createIndex(
            'idx-post_category-category_id',
            'post_category',
            'category_id'
        );

        // add foreign key for table `category`
        $this->addForeignKey(
            'fk-post_category-category_id',
            'post_category',
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
            'fk-post_category-post_id',
            'post_category'
        );

        // drops index for column `post_id`
        $this->dropIndex(
            'idx-post_category-post_id',
            'post_category'
        );

        // drops foreign key for table `category`
        $this->dropForeignKey(
            'fk-post_category-category_id',
            'post_category'
        );

        // drops index for column `category_id`
        $this->dropIndex(
            'idx-post_category-category_id',
            'post_category'
        );

        $this->dropTable('post_category');
    }
}
