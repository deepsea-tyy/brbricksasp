<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%order_settle}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property int|null $order_id
 * @property float|null $money
 * @property int|null $status 1已入账2订单取消
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class OrderSettle extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_settle}}';
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
            [['owner_id', 'user_id', 'order_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['money'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner_id' => 'Owner ID',
            'user_id' => 'User ID',
            'order_id' => 'Order ID',
            'money' => 'Money',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
