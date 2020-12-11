<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%file_relation}}".
 *
 * @property int|null $object_id
 * @property string|null $file_id
 * @property int|null $type 1商品图片2单品图片3商品评论图片
 * @property int|null $sort
 */
class FileRelation extends \bricksasp\base\BaseActiveRecord
{
    const TYPE_GOODS = 1;
    const TYPE_PRODUCT = 2;
    const TYPE_GOODS_CMT = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%file_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['object_id', 'type', 'sort'], 'integer'],
            [['file_id'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'object_id' => 'Object ID',
            'file_id' => 'Image ID',
            'type' => 'Type',
            'sort' => 'Sort',
        ];
    }
}
