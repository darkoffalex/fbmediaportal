<?php

use yii\db\Migration;

/**
 * Handles adding fb_token to table `user`.
 */
class m170223_200901_add_fb_token_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('user', 'fb_auth_token', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('user', 'fb_auth_token');
    }
}
