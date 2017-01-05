<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "category_trl".
 *
 * @property integer $id
 * @property integer $category_id
 * @property string $lng
 * @property string $name
 * @property string $description
 * @property string $meta_keywords
 * @property string $meta_description
 * @property string $seo_alias
 *
 * @property Category $category
 */
class CategoryTrlDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category_trl';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id'], 'integer'],
            [['description', 'meta_description'], 'string'],
            [['lng', 'name', 'meta_keywords', 'seo_alias'], 'string', 'max' => 255],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category ID',
            'lng' => 'Lng',
            'name' => 'Name',
            'description' => 'Description',
            'meta_keywords' => 'Meta Keywords',
            'meta_description' => 'Meta Description',
            'seo_alias' => 'Seo Alias',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }
}
