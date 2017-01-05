<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "label".
 *
 * @property integer $id
 * @property string $source_word
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by_id
 * @property integer $updated_by_id
 *
 * @property LabelTrl[] $labelTrls
 */
class LabelDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'label';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['source_word'], 'required'],
            [['source_word'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by_id', 'updated_by_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'source_word' => 'Source Word',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLabelTrls()
    {
        return $this->hasMany(LabelTrl::className(), ['label_id' => 'id']);
    }
}
