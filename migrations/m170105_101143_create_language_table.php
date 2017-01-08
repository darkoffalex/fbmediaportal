<?php

use yii\db\Migration;

/**
 * Handles the creation of table `language`.
 */
class m170105_101143_create_language_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('language', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'self_name' => $this->string(),
            'prefix' => $this->string(3),
            'icon_filename' => $this->string(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by_id' => $this->integer(),
            'updated_by_id' => $this->integer(),
        ]);

        $this->insert('language',[
            'name' => 'Russian',
            'self_name' => 'Русский',
            'prefix' => 'ru',
            'created_at' => date('Y-m-d H:i:s',time()),
            'updated_at' => date('Y-m-d H:i:s',time()),
        ]);

        $this->insert('language',[
            'name' => 'English',
            'self_name' => 'English',
            'prefix' => 'en',
            'created_at' => date('Y-m-d H:i:s',time()),
            'updated_at' => date('Y-m-d H:i:s',time()),
        ]);
    }


    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('language');
    }
}
