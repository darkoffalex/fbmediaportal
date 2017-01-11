<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "banner".
 *
 * @property integer $id
 * @property string $name
 * @property integer $type_id
 * @property string $code
 * @property string $custom_html
 * @property string $image_filename
 * @property integer $clicks
 * @property integer $created_by_id
 * @property integer $updated_by_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $link
 *
 * @property BannerDisplay[] $bannerDisplays
 */
class BannerDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'banner';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['type_id', 'clicks', 'created_by_id', 'updated_by_id'], 'integer'],
            [['code', 'custom_html', 'link'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'image_filename'], 'string', 'max' => 255],
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
            'type_id' => 'Type ID',
            'code' => 'Code',
            'custom_html' => 'Custom Html',
            'image_filename' => 'Image Filename',
            'clicks' => 'Clicks',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'link' => 'Link',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBannerDisplays()
    {
        return $this->hasMany(BannerDisplay::className(), ['banner_id' => 'id']);
    }
}
