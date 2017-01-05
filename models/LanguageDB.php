<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "language".
 *
 * @property integer $id
 * @property string $name
 * @property string $self_name
 * @property string $prefix
 * @property string $icon_filename
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by_id
 * @property integer $updated_by_id
 */
class LanguageDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'language';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by_id', 'updated_by_id'], 'integer'],
            [['name', 'self_name', 'icon_filename'], 'string', 'max' => 255],
            [['prefix'], 'string', 'max' => 3],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'self_name' => 'Self Name',
            'prefix' => 'Prefix',
            'icon_filename' => 'Icon Filename',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
        ];
    }
}
