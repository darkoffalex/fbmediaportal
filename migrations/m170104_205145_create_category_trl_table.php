<?php

use yii\db\Migration;

/**
 * Handles the creation of table `category_trl`.
 * Has foreign keys to the tables:
 *
 * - `category`
 */
class m170104_205145_create_category_trl_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('category_trl', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer(),
            'lng' => $this->string(),
            'name' => $this->string(),
            'description' => $this->text(),
        ]);

        // creates index for column `category_id`
        $this->createIndex(
            'idx-category_trl-category_id',
            'category_trl',
            'category_id'
        );

        // add foreign key for table `category`
        $this->addForeignKey(
            'fk-category_trl-category_id',
            'category_trl',
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
        // drops foreign key for table `category`
        $this->dropForeignKey(
            'fk-category_trl-category_id',
            'category_trl'
        );

        // drops index for column `category_id`
        $this->dropIndex(
            'idx-category_trl-category_id',
            'category_trl'
        );

        $this->dropTable('category_trl');
    }
}
