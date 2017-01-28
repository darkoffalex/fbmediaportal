<?php

use yii\db\Migration;

/**
 * Handles the creation of table `post`.
 * Has foreign keys to the tables:
 *
 * - `user`
 */
class m170105_180807_create_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'fb_sync_id' => $this->text(),
            'fb_sync_token' => $this->text(),
            'content_type_id' => $this->integer(),
            'status_id' => $this->integer(),
            'type_id' => $this->integer(),
            'name' => $this->string()->notNull(),
            'author_id' => $this->integer(),
            'author_custom_name' => $this->string(),
            'source_url' => $this->text(),
            'sticky_position_main' => $this->integer(),
            'stats_after_vote' => $this->integer(),
            'votes_only_authorized' => $this->integer(),
            'video_key_yt' => $this->text(),
            'video_key_fb' => $this->text(),
            'published_at' => $this->dateTime(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by_id' => $this->integer(),
            'updated_by_id' => $this->integer(),
        ]);

        // creates index for column `author_id`
        $this->createIndex(
            'idx-post-author_id',
            'post',
            'author_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-post-author_id',
            'post',
            'author_id',
            'user',
            'id',
            'SET NULL',
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
            'fk-post-author_id',
            'post'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            'idx-post-author_id',
            'post'
        );

        $this->dropTable('post');
    }
}
