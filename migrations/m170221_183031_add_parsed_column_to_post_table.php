<?php

use yii\db\Migration;

/**
 * Handles adding parsed to table `post`.
 */
class m170221_183031_add_parsed_column_to_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('post', 'is_parsed', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('post', 'is_parsed');
    }
}
