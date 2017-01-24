<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "post_group".
 *
 * @property integer $id
 * @property string $name
 * @property string $url
 * @property integer $created_by_id
 * @property integer $updated_by_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $fb_sync_id
 * @property integer $is_group
 *
 * @property Post[] $posts
 * @property StockRecommendation[] $stockRecommendations
 */
class PostGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['url', 'fb_sync_id'], 'string'],
            [['created_by_id', 'updated_by_id', 'is_group'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'url' => 'Url',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'fb_sync_id' => 'Fb Sync ID',
            'is_group' => 'Is Group',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::className(), ['group_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockRecommendations()
    {
        return $this->hasMany(StockRecommendation::className(), ['group_id' => 'id']);
    }
}
