<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%runerrands_cost_weight}}".
 *
 * @property int $id
 * @property int|null $cost_id
 * @property string|null $title
 * @property float|null $price
 */
class RunerrandsCostWeight extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%runerrands_cost_weight}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cost_id'], 'integer'],
            [['price'], 'number'],
            [['title'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cost_id' => 'Cost ID',
            'title' => 'Title',
            'price' => 'Price',
        ];
    }
}
