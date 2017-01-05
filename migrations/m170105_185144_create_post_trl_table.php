<?php

use yii\db\Migration;

/**
 * Handles the creation of table `post_trl`.
 * Has foreign keys to the tables:
 *
 * - `post`
 */
class m170105_185144_create_post_trl_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('post_trl', [
            'id' => $this->primaryKey(),
            'post_id' => $this->integer(),
            'lng' => $this->string(5),
            'name' => $this->string(),
            'small_text' => $this->text(),
            'text' => $this->text(),
            'question' => $this->text(),
        ]);

        // creates index for column `post_id`
        $this->createIndex(
            'idx-post_trl-post_id',
            'post_trl',
            'post_id'
        );

        // add foreign key for table `post`
        $this->addForeignKey(
            'fk-post_trl-post_id',
            'post_trl',
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
            'fk-post_trl-post_id',
            'post_trl'
        );

        // drops index for column `post_id`
        $this->dropIndex(
            'idx-post_trl-post_id',
            'post_trl'
        );

        $this->dropTable('post_trl');
    }
}
