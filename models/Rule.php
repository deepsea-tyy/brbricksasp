<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%rule}}".
 *
 * @property int $id
 * @property string|null $name 规则名称
 * @property int|null $type
 * @property string|null $rule 规则文件
 */
class Rule extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%rule}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['rule'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'type' => 'Type',
            'rule' => 'Rule',
        ];
    }
}
