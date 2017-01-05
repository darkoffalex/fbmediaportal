<?php

use yii\db\Migration;

/**
 * Handles adding ips to table `post`.
 */
class m170105_193534_add_ips_column_to_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post', 'voted_ips', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post', 'voted_ips');
    }
}
