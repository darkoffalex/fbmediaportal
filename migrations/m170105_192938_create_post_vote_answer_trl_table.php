<?php

use yii\db\Migration;

/**
 * Handles the creation of table `post_vote_answer_trl`.
 * Has foreign keys to the tables:
 *
 * - `post_vote_answer`
 */
class m170105_192938_create_post_vote_answer_trl_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('post_vote_answer_trl', [
            'id' => $this->primaryKey(),
            'answer_id' => $this->integer(),
            'lng' => $this->string(5),
            'text' => $this->text(),
        ]);

        // creates index for column `answer_id`
        $this->createIndex(
            'idx-post_vote_answer_trl-answer_id',
            'post_vote_answer_trl',
            'answer_id'
        );

        // add foreign key for table `post_vote_answer`
        $this->addForeignKey(
            'fk-post_vote_answer_trl-answer_id',
            'post_vote_answer_trl',
            'answer_id',
            'post_vote_answer',
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
        // drops foreign key for table `post_vote_answer`
        $this->dropForeignKey(
            'fk-post_vote_answer_trl-answer_id',
            'post_vote_answer_trl'
        );

        // drops index for column `answer_id`
        $this->dropIndex(
            'idx-post_vote_answer_trl-answer_id',
            'post_vote_answer_trl'
        );

        $this->dropTable('post_vote_answer_trl');
    }
}
