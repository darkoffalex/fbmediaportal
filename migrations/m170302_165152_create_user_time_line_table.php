<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_time_line`.
 * Has foreign keys to the tables:
 *
 * - `user`
 * - `post`
 * - `comment`
 */
class m170302_165152_create_user_time_line_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('user_time_line', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'post_id' => $this->integer(),
            'comment_id' => $this->integer(),
            'published_at' => $this->dateTime(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            'idx-user_time_line-user_id',
            'user_time_line',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-user_time_line-user_id',
            'user_time_line',
            'user_id',
            'user',
            'id',
            'CASCADE',
            'NO ACTION'
        );

        // creates index for column `post_id`
        $this->createIndex(
            'idx-user_time_line-post_id',
            'user_time_line',
            'post_id'
        );

        // add foreign key for table `post`
        $this->addForeignKey(
            'fk-user_time_line-post_id',
            'user_time_line',
            'post_id',
            'post',
            'id',
            'CASCADE',
            'NO ACTION'
        );

        // creates index for column `comment_id`
        $this->createIndex(
            'idx-user_time_line-comment_id',
            'user_time_line',
            'comment_id'
        );

        // add foreign key for table `comment`
        $this->addForeignKey(
            'fk-user_time_line-comment_id',
            'user_time_line',
            'comment_id',
            'comment',
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
        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-user_time_line-user_id',
            'user_time_line'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-user_time_line-user_id',
            'user_time_line'
        );

        // drops foreign key for table `post`
        $this->dropForeignKey(
            'fk-user_time_line-post_id',
            'user_time_line'
        );

        // drops index for column `post_id`
        $this->dropIndex(
            'idx-user_time_line-post_id',
            'user_time_line'
        );

        // drops foreign key for table `comment`
        $this->dropForeignKey(
            'fk-user_time_line-comment_id',
            'user_time_line'
        );

        // drops index for column `comment_id`
        $this->dropIndex(
            'idx-user_time_line-comment_id',
            'user_time_line'
        );

        $this->dropTable('user_time_line');
    }
}
