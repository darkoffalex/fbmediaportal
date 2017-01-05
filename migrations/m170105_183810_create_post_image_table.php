<?php

use yii\db\Migration;

/**
 * Handles the creation of table `post_image`.
 * Has foreign keys to the tables:
 *
 * - `post`
 */
class m170105_183810_create_post_image_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('post_image', [
            'id' => $this->primaryKey(),
            'post_id' => $this->integer(),
            'file_path' => $this->string(),
            'file_url' => $this->text(),
            'is_external' => $this->integer(),
            'status_id' => $this->integer(),
            'priority' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by_id' => $this->integer(),
            'updated_by_id' => $this->integer(),
        ]);

        // creates index for column `post_id`
        $this->createIndex(
            'idx-post_image-post_id',
            'post_image',
            'post_id'
        );

        // add foreign key for table `post`
        $this->addForeignKey(
            'fk-post_image-post_id',
            'post_image',
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
            'fk-post_image-post_id',
            'post_image'
        );

        // drops index for column `post_id`
        $this->dropIndex(
            'idx-post_image-post_id',
            'post_image'
        );

        $this->dropTable('post_image');
    }
}
