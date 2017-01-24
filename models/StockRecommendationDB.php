<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "stock_recommendation".
 *
 * @property integer $id
 * @property integer $author_id
 * @property string $category_tag
 * @property integer $group_id
 * @property integer $category_id
 * @property integer $created_by_id
 * @property integer $updated_by_id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $reason_type_id
 *
 * @property User $author
 * @property Category $category
 * @property PostGroup $group
 */
class StockRecommendationDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stock_recommendation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['author_id', 'group_id', 'category_id', 'created_by_id', 'updated_by_id', 'reason_type_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['category_tag'], 'string', 'max' => 255],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['category_id' => 'id']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => PostGroup::className(), 'targetAttribute' => ['group_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'author_id' => 'Author ID',
            'category_tag' => 'Category Tag',
            'group_id' => 'Group ID',
            'category_id' => 'Category ID',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'reason_type_id' => 'Reason Type ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(PostGroup::className(), ['id' => 'group_id']);
    }
}
