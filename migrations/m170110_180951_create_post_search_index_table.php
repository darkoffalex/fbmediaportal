<?php

use yii\db\Migration;

/**
 * Handles the creation of table `post_search_index`.
 * Has foreign keys to the tables:
 *
 * - `post`
 */
class m170110_180951_create_post_search_index_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('post_search_index', [
            'id' => $this->primaryKey(),
            'text' => $this->text(),
            'post_id' => $this->integer(),
        ]);

        // creates index for column `post_id`
        $this->createIndex(
            'idx-post_search_index-post_id',
            'post_search_index',
            'post_id'
        );

        // add foreign key for table `post`
        $this->addForeignKey(
            'fk-post_search_index-post_id',
            'post_search_index',
            'post_id',
            'post',
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
            'fk-post_search_index-post_id',
            'post_search_index'
        );

        // drops index for column `post_id`
        $this->dropIndex(
            'idx-post_search_index-post_id',
            'post_search_index'
        );

        $this->dropTable('post_search_index');
    }
}
