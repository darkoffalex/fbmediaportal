<?php

use yii\db\Migration;

/**
 * Handles adding meta to table `category_trl`.
 */
class m170105_100551_add_meta_column_to_category_trl_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('category_trl', 'meta_keywords', $this->string());
        $this->addColumn('category_trl', 'meta_description', $this->text());
        $this->addColumn('category_trl', 'seo_alias', $this->string());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('category_trl', 'meta_keywords');
        $this->dropColumn('category_trl', 'meta_description');
        $this->dropColumn('category_trl', 'seo_alias');
    }
}
