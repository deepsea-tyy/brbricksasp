<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%order_item}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $owner_id
 * @property int|null $order_id
 * @property int|null $goods_id
 * @property int|null $product_id
 * @property string|null $name 商品名称
 * @property string|null $barcode 商品条码
 * @property string|null $brief 商品简介
 * @property float|null $price 售价
 * @property float|null $costprice 单品成本价单价
 * @property float|null $mktprice 单品市场价
 * @property string|null $image_id 图片
 * @property int|null $num 数量
 * @property float|null $pay_price 支付总金额
 * @property float|null $pmt_price 优惠总金额
 * @property float|null $weight 总重量
 * @property float|null $volume 总体积
 * @property int|null $delivery_num 交货数量
 * @property int|null $created_at
 * @property int|null $updated_at 更新时间
 */
class OrderItem extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_item}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'owner_id', 'order_id', 'goods_id', 'product_id', 'num', 'delivery_num', 'created_at', 'updated_at'], 'integer'],
            [['price', 'costprice', 'mktprice', 'pay_price', 'pmt_price', 'weight', 'volume'], 'number'],
            [['name'], 'string', 'max' => 32],
            [['barcode'], 'string', 'max' => 30],
            [['brief'], 'string', 'max' => 255],
            [['image_id'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'owner_id' => 'Owner ID',
            'order_id' => 'Order ID',
            'goods_id' => 'Goods ID',
            'product_id' => 'Product ID',
            'name' => 'Name',
            'barcode' => 'Barcode',
            'brief' => 'Brief',
            'price' => 'Price',
            'costprice' => 'Costprice',
            'mktprice' => 'Mktprice',
            'image_id' => 'Image ID',
            'num' => 'Num',
            'pay_price' => 'Pay Price',
            'pmt_price' => 'Pmt Price',
            'weight' => 'Weight',
            'volume' => 'Volume',
            'delivery_num' => 'Delivery Num',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
