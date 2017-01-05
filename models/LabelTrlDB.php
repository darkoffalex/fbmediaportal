<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "label_trl".
 *
 * @property integer $id
 * @property integer $label_id
 * @property string $lng
 * @property string $word
 *
 * @property Label $label
 */
class LabelTrlDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'label_trl';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['label_id'], 'integer'],
            [['word'], 'string'],
            [['lng'], 'string', 'max' => 5],
            [['label_id'], 'exist', 'skipOnError' => true, 'targetClass' => Label::className(), 'targetAttribute' => ['label_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'label_id' => 'Label ID',
            'lng' => 'Lng',
            'word' => 'Word',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLabel()
    {
        return $this->hasOne(Label::className(), ['id' => 'label_id']);
    }
}
