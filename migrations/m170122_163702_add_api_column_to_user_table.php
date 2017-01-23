<?php

use yii\db\Migration;

/**
 * Handles adding api to table `user`.
 */
class m170122_163702_add_api_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('user', 'api_key', $this->string());
        $this->addColumn('user', 'is_basic', $this->integer());

        $this->update('user', [
            'api_key' => (string)rand(0,9999999),
            'is_basic' => (int)true,
        ],['id' => 1]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('user', 'api_key');
        $this->dropColumn('user', 'is_basic');
    }
}
