<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%order_pay}}".
 *
 * @property int $id 支付单号 第三方平台交易流水号
 * @property int|null $order_id
 * @property int|null $user_id
 * @property int|null $type 1一次性支付 2部分支付
 * @property int|null $status 1支付成功2其他
 * @property float|null $money 支付金额
 * @property string|null $pay_type 支付类型编码
 * @property string|null $pay_info 回调原始参数
 * @property string|null $ip
 * @property string|null $third_id 三方流水号
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class OrderPay extends \bricksasp\base\BaseActiveRecord
{
    const TYPE_ALL = 1;
    const TYPE_PART = 2;

    const STATUS_SUCCESS = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_pay}}';
    }

    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
            [
                'class' => \bricksasp\common\SnBehavior::className(),
                'attribute' => 'id',
                'type' => \bricksasp\common\SnBehavior::SN_PAY,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'order_id', 'user_id', 'type', 'status', 'created_at', 'updated_at'], 'integer'],
            [['money'], 'number'],
            [['pay_info'], 'string'],
            [['pay_type'], 'string', 'max' => 8],
            [['ip', 'third_id'], 'string', 'max' => 50],
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
            'order_id' => 'Order ID',
            'user_id' => 'User ID',
            'type' => 'Type',
            'status' => 'Status',
            'money' => 'Money',
            'pay_type' => 'Pay Type',
            'pay_info' => 'Pay Info',
            'ip' => 'Ip',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
