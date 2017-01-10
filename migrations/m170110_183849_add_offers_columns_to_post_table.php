<?php

use yii\db\Migration;

/**
 * Handles adding offers to table `post`.
 */
class m170110_183849_add_offers_columns_to_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post', 'offer_category_tag', $this->string());
        $this->addColumn('post', 'offer_author_tag', $this->string());
        $this->addColumn('post', 'offer_group_fb_id', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post', 'offer_category_tag');
        $this->dropColumn('post', 'offer_author_tag');
        $this->dropColumn('post', 'offer_group_fb_id');
    }
}
