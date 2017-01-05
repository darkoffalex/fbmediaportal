<?php

use yii\db\Migration;

/**
 * Handles the creation of table `post_vote_answer`.
 * Has foreign keys to the tables:
 *
 * - `post`
 */
class m170105_192644_create_post_vote_answer_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('post_vote_answer', [
            'id' => $this->primaryKey(),
            'post_id' => $this->integer(),
            'voted_qnt' => $this->integer(),
            'priority' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by_id' => $this->integer(),
            'updated_by_id' => $this->integer(),
        ]);

        // creates index for column `post_id`
        $this->createIndex(
            'idx-post_vote_answer-post_id',
            'post_vote_answer',
            'post_id'
        );

        // add foreign key for table `post`
        $this->addForeignKey(
            'fk-post_vote_answer-post_id',
            'post_vote_answer',
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
            'fk-post_vote_answer-post_id',
            'post_vote_answer'
        );

        // drops index for column `post_id`
        $this->dropIndex(
            'idx-post_vote_answer-post_id',
            'post_vote_answer'
        );

        $this->dropTable('post_vote_answer');
    }
}
