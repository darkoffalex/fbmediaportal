<?php

use yii\db\Migration;

/**
 * Handles the creation of table `post_image_trl`.
 * Has foreign keys to the tables:
 *
 * - `post_image`
 */
class m170105_184227_create_post_image_trl_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('post_image_trl', [
            'id' => $this->primaryKey(),
            'post_image_id' => $this->integer(),
            'lng' => $this->string(5),
            'signature' => $this->string(),
            'name' => $this->string(),
        ]);

        // creates index for column `post_image_id`
        $this->createIndex(
            'idx-post_image_trl-post_image_id',
            'post_image_trl',
            'post_image_id'
        );

        // add foreign key for table `post_image`
        $this->addForeignKey(
            'fk-post_image_trl-post_image_id',
            'post_image_trl',
            'post_image_id',
            'post_image',
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
        // drops foreign key for table `post_image`
        $this->dropForeignKey(
            'fk-post_image_trl-post_image_id',
            'post_image_trl'
        );

        // drops index for column `post_image_id`
        $this->dropIndex(
            'idx-post_image_trl-post_image_id',
            'post_image_trl'
        );

        $this->dropTable('post_image_trl');
    }
}
