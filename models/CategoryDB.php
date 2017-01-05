<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "category".
 *
 * @property integer $id
 * @property integer $parent_category_id
 * @property string $name
 * @property integer $status_id
 * @property integer $priority
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by_id
 * @property integer $updated_by_id
 * @property string $icon_file
 *
 * @property CategoryTrl[] $categoryTrls
 * @property PostCategory[] $postCategories
 * @property Post[] $posts
 */
class CategoryDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_category_id', 'status_id', 'priority', 'created_by_id', 'updated_by_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'icon_file'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_category_id' => 'Parent Category ID',
            'name' => 'Name',
            'status_id' => 'Status ID',
            'priority' => 'Priority',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
            'icon_file' => 'Icon File',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryTrls()
    {
        return $this->hasMany(CategoryTrl::className(), ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostCategories()
    {
        return $this->hasMany(PostCategory::className(), ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::className(), ['id' => 'post_id'])->viaTable('post_category', ['category_id' => 'id']);
    }
}
