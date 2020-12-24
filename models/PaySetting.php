<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\pay\Wechat;

/**
 * This is the model class for table "{{%pay_setting}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property int|null $platform
 * @property string|null $config
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class PaySetting extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pay_setting}}';
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
            [['owner_id', 'user_id', 'platform', 'status', 'created_at', 'updated_at'], 'integer'],
            [['config'], 'vaildConfig'],
            [['config'], 'string'],
            [['status'], 'default', 'value' => 1],
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
            'platform' => 'Platform',
            'config' => 'Config',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function vaildConfig()
    {
        $this->config = json_encode($this->config, JSON_UNESCAPED_UNICODE);
    }

    public function saveData($data)
    {
        if (!$this->checkArray($data,['config'])) {
            return false;
        }
        $this->load($this->formatData($data));
        return $this->save();
    }

    public function createOrderPay($data)
    {
        $order = Order::find()->select(['pay_price', 'payed_price', 'pay_status'])->where([
            'id'=>$data['order_id'],
            'user_id'=>$data['current_user_id'],
            'pay_status'=>[Order::PAY_NO, Order::PAY_PART]
        ])->one();
        if (!$order) {
            Tools::breakOff('订单不存在或已支付');
        }

        $pay_type = $data['pay_type'];
        if ($order->pay_status == Order::PAY_PART) {
            $price = $order->pay_price - $order->payed_price;
            $type = OrderPay::TYPE_PART;
        }else{
            $price = $order->pay_price;
            $type = OrderPay::TYPE_ALL;
        }

        $model = new OrderPay();
        $model->load([
            'order_id' => $data['order_id'],
            'user_id' => $data['current_user_id'],
            'money' => $price,
            'type' => $type,
            'pay_type' => $pay_type,
            'ip' => Yii::$app->request->userIp,
        ]);
        if ($model->save()) {
            if ($data['pay_platform'] == 2) {
                $pay = Yii::createObject([
                    'class' => Wechat::className(),
                    'money' => $price,
                    'owner_id' => $data['current_owner_id'],
                    'user_id' => $model->user_id,
                    'pay_id' => $model->id,
                    'ip' => $model->ip,
                ]);
            }
            
        }
    }
}