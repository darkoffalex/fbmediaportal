<?php

use yii\db\Migration;

/**
 * Handles the creation of table `stock_recommendation`.
 * Has foreign keys to the tables:
 *
 * - `user`
 * - `post_group`
 * - `category`
 */
class m170124_163253_create_stock_recommendation_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('stock_recommendation', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer(),
            'category_tag' => $this->string(),
            'group_id' => $this->integer(),
            'category_id' => $this->integer(),
            'created_by_id' => $this->integer(),
            'updated_by_id' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);

        // creates index for column `author_id`
        $this->createIndex(
            'idx-stock_recommendation-author_id',
            'stock_recommendation',
            'author_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-stock_recommendation-author_id',
            'stock_recommendation',
            'author_id',
            'user',
            'id',
            'CASCADE',
            'NO ACTION'
        );

        // creates index for column `group_id`
        $this->createIndex(
            'idx-stock_recommendation-group_id',
            'stock_recommendation',
            'group_id'
        );

        // add foreign key for table `post_group`
        $this->addForeignKey(
            'fk-stock_recommendation-group_id',
            'stock_recommendation',
            'group_id',
            'post_group',
            'id',
            'CASCADE',
            'NO ACTION'
        );

        // creates index for column `category_id`
        $this->createIndex(
            'idx-stock_recommendation-category_id',
            'stock_recommendation',
            'category_id'
        );

        // add foreign key for table `category`
        $this->addForeignKey(
            'fk-stock_recommendation-category_id',
            'stock_recommendation',
            'category_id',
            'category',
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
            'fk-stock_recommendation-author_id',
            'stock_recommendation'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            'idx-stock_recommendation-author_id',
            'stock_recommendation'
        );

        // drops foreign key for table `post_group`
        $this->dropForeignKey(
            'fk-stock_recommendation-group_id',
            'stock_recommendation'
        );

        // drops index for column `group_id`
        $this->dropIndex(
            'idx-stock_recommendation-group_id',
            'stock_recommendation'
        );

        // drops foreign key for table `category`
        $this->dropForeignKey(
            'fk-stock_recommendation-category_id',
            'stock_recommendation'
        );

        // drops index for column `category_id`
        $this->dropIndex(
            'idx-stock_recommendation-category_id',
            'stock_recommendation'
        );

        $this->dropTable('stock_recommendation');
    }
}
