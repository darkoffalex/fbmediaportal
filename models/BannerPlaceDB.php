<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "banner_place".
 *
 * @property integer $id
 * @property string $name
 * @property string $alias
 * @property integer $created_by_id
 * @property integer $updated_by_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property BannerDisplay[] $bannerDisplays
 */
class BannerPlaceDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'banner_place';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'alias'], 'required'],
            [['created_by_id', 'updated_by_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'alias'], 'string', 'max' => 255],
            [['alias'], 'unique'],
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
            'alias' => 'Alias',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBannerDisplays()
    {
        return $this->hasMany(BannerDisplay::className(), ['place_id' => 'id']);
    }
}
