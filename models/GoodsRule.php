<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%goods_rule}}".
 *
 * @property int $id
 * @property int|null $type 1商品2单品
 * @property int|null $object_id
 * @property string|null $rule 规则文件路径
 */
class GoodsRule extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_rule}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'type', 'object_id'], 'integer'],
            [['rule'], 'string', 'max' => 64],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'object_id' => 'Object ID',
            'rule' => 'Rule',
        ];
    }
}
