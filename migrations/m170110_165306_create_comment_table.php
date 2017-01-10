<?php

use yii\db\Migration;

/**
 * Handles the creation of table `comment`.
 * Has foreign keys to the tables:
 *
 * - `post`
 * - `user`
 */
class m170110_165306_create_comment_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('comment', [
            'id' => $this->primaryKey(),
            'post_id' => $this->integer(),
            'author_id' => $this->integer(),
            'text' => $this->text(),
            'answer_to_id' => $this->integer(),
            'fb_sync_id' => $this->text(),
            'fb_sync_token' => $this->text(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by_id' => $this->integer(),
            'updated_by_id' => $this->integer(),
        ]);

        // creates index for column `post_id`
        $this->createIndex(
            'idx-comment-post_id',
            'comment',
            'post_id'
        );

        // add foreign key for table `post`
        $this->addForeignKey(
            'fk-comment-post_id',
            'comment',
            'post_id',
            'post',
            'id',
            'CASCADE',
            'NO ACTION'
        );

        // creates index for column `author_id`
        $this->createIndex(
            'idx-comment-author_id',
            'comment',
            'author_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-comment-author_id',
            'comment',
            'author_id',
            'user',
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
            'fk-comment-post_id',
            'comment'
        );

        // drops index for column `post_id`
        $this->dropIndex(
            'idx-comment-post_id',
            'comment'
        );

        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-comment-author_id',
            'comment'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            'idx-comment-author_id',
            'comment'
        );

        $this->dropTable('comment');
    }
}
