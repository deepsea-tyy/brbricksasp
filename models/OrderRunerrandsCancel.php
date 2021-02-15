<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%order_runerrands_cancel}}".
 *
 * @property int|null $order_id
 * @property int|null $user_id
 * @property int|null $created_at
 */
class OrderRunerrandsCancel extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_runerrands_cancel}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'user_id', 'created_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'order_id' => 'Order ID',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
        ];
    }
}
