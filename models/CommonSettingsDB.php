<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "common_settings".
 *
 * @property integer $id
 * @property string $header_logo_filename
 * @property string $footer_content
 */
class CommonSettingsDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'common_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['footer_content'], 'string'],
            [['header_logo_filename'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'header_logo_filename' => 'Header Logo Filename',
            'footer_content' => 'Footer Content',
        ];
    }
}
