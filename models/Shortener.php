<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%shortener}}".
 *
 * @property int $id
 * @property string|null $key 显示值
 * @property string|null $val
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Shortener extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shortener}}';
    }

    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['val'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['key'], 'string', 'max' => 32],
            [['key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'val' => 'Val',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    
    public function saveData($data)
    {
        $this->load($this->formatData($data));
        return $this->save();
    }
}
