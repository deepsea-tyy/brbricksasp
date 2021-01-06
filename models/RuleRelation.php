<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%rule_relation}}".
 *
 * @property int $id
 * @property int|null $type 1商品2单品
 * @property int|null $object_id
 * @property int|null $rule_id
 */
class RuleRelation extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%rule_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'object_id', 'rule_id'], 'integer'],
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
            'rule_id' => 'Rule ID',
        ];
    }
}
