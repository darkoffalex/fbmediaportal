<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "banner_display".
 *
 * @property integer $id
 * @property integer $banner_id
 * @property integer $place_id
 * @property string $start_at
 * @property string $end_at
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by_id
 * @property integer $updated_by_id
 *
 * @property Banner $banner
 * @property BannerPlace $place
 */
class BannerDisplayDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'banner_display';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['banner_id', 'place_id', 'created_by_id', 'updated_by_id'], 'integer'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'safe'],
            [['banner_id'], 'exist', 'skipOnError' => true, 'targetClass' => Banner::className(), 'targetAttribute' => ['banner_id' => 'id']],
            [['place_id'], 'exist', 'skipOnError' => true, 'targetClass' => BannerPlace::className(), 'targetAttribute' => ['place_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'banner_id' => 'Banner ID',
            'place_id' => 'Place ID',
            'start_at' => 'Start At',
            'end_at' => 'End At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBanner()
    {
        return $this->hasOne(Banner::className(), ['id' => 'banner_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlace()
    {
        return $this->hasOne(BannerPlace::className(), ['id' => 'place_id']);
    }
}
