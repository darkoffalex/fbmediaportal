<?php

use yii\db\Migration;

/**
 * Handles adding group_id to table `post`.
 * Has foreign keys to the tables:
 *
 * - `post_group`
 */
class m170110_163443_add_group_id_column_to_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn('post','source_url');

        $this->addColumn('post', 'group_id', $this->integer());

        // creates index for column `group_id`
        $this->createIndex(
            'idx-post-group_id',
            'post',
            'group_id'
        );

        // add foreign key for table `post_group`
        $this->addForeignKey(
            'fk-post-group_id',
            'post',
            'group_id',
            'post_group',
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
        // drops foreign key for table `post_group`
        $this->dropForeignKey(
            'fk-post-group_id',
            'post'
        );

        // drops index for column `group_id`
        $this->dropIndex(
            'idx-post-group_id',
            'post'
        );

        $this->dropColumn('post', 'group_id');

        $this->addColumn('post','source_url','text');
    }
}
