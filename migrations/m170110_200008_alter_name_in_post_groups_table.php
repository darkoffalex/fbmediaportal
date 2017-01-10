<?php

use yii\db\Migration;

class m170110_200008_alter_name_in_post_groups_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->alterColumn('post_group','name',$this->string()->notNull());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->alterColumn('post_group','name',$this->string()->null());
    }
}
