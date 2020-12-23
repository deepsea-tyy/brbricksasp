<?php
namespace bricksasp\models\form;

use Yii;

/**
 * 订单验证
 */
class OrderValidate extends \bricksasp\base\FormValidate
{
    const CREATE_ORDER = 'create_order';
    const CREATE_BILL = 'create_bill';
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['store_id', 'lat', 'lon','ship_id', 'status', 'pay_now', 'show_id', 'pay_platform'], 'number'],
            [['pay_type'], 'string'],
            [['pay_platform'], 'in', 'range'=> [2, 3]],
            [['pay_type'], 'in', 'range'=> ['app', 'bar', 'lite', 'pub', 'qr', 'wap', 'web']],
            [['pay_platform', 'pay_type','order_id'], 'required', 'on' => [self::CREATE_BILL]],
            ['store_id', 'vaildStoreId', 'on'=> [self::CREATE_ORDER]],
            ['buy_now', 'vaildBuyNow', 'on'=> [self::CREATE_ORDER]],

            [['cart_ids', 'buy_now', 'coupon_ids'],'required', 'when'=>function ($model, $attribute)
            {
                if (!$model->cart_ids && !$model->buy_now) {
                    return true;
                }
                return false;
            }, 'message' => '购物车或单品二选一必填', 'on' => [self::CREATE_ORDER]],
        ];
    }

    /**
     * 使用场景
     */
    public function scenarios()
    {
        return [
            self::CREATE_ORDER => ['cart_ids', 'coupon_ids', 'buy_now', 'ship_id', 'store_id', 'lat', 'lon', 'pay_now', 'show_id', 'pay_platform', 'pay_type'],
            self::CREATE_BILL => ['pay_platform', 'pay_type', 'order_id'],
        ];
    }

    public function vaildBuyNow()
    {
        if ($this->checkArray([$this->cart_ids, $this->buy_now, $this->coupon_ids], ['buy_now','cart_ids','coupon_ids'])) {
            if ($this->buy_now) {
                if ($this->buy_now['num'] < 1) {
                    $this->addError('buy_now.num', '购买数量不能小于1');
                }
            }
        }
    }

    public function vaildStoreId()
    {
        if ($this->store_id && (!$this->lat || !$this->lon)) {
            $this->addError('lat', '经纬度不能为空');
        }
    }

    public function attributeLabels()
    {
        return [
            'cart_ids' => '购物车',
            'store_id' => '就近发货',
            'lat' => '经度',
            'lon' => '纬度',
        ];
    }
}