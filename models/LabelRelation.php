<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%label_relation}}".
 *
 * @property int|null $object_id
 * @property int|null $label_id
 * @property int|null $type 1文章表情2商品标签
 * @property int|null $sort
 */
class LabelRelation extends \bricksasp\base\BaseActiveRecord
{
    const TYPT_ARTICLE = 1;
    const TYPT_GOODS = 2;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%label_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['object_id', 'label_id', 'type', 'sort'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'object_id' => 'Object ID',
            'label_id' => 'Label ID',
            'type' => 'Type',
            'sort' => 'Sort',
        ];
    }
}
