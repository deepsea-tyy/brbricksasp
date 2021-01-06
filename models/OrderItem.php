<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%order_item}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $owner_id 拆单归属
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
 * @property int|null $ship_area_id 收货地区ID
 * @property string|null $ship_address 收货详细地址
 * @property string|null $ship_name 收货人姓名
 * @property string|null $ship_phone 收货电话
 * @property string|null $logistics_name 配送方式名称
 * @property string|null $logistics_id 物流号
 * @property int|null $is_comment 1已评论
 * @property int|null $comment_at 评论时间
 * @property int|null $is_receive 1已收货
 * @property int|null $receive_at
 * @property int|null $is_exchange 1已换货
 * @property int|null $exchange_at
 * @property int|null $is_return 1已退货
 * @property int|null $return_at
 * @property int|null $confirm 确单状态1确认
 * @property int|null $confirm_at 确认时间
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class OrderItem extends \bricksasp\base\BaseActiveRecord
{
    const COMMENT_YES = 1;
    
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
            [['user_id', 'owner_id', 'order_id', 'goods_id', 'product_id', 'num', 'delivery_num', 'ship_area_id', 'is_comment', 'comment_at', 'is_receive', 'receive_at', 'is_exchange', 'exchange_at', 'is_return', 'return_at', 'confirm', 'confirm_at', 'created_at', 'updated_at'], 'integer'],
            [['price', 'costprice', 'mktprice', 'pay_price', 'pmt_price', 'weight', 'volume'], 'number'],
            [['name', 'logistics_name'], 'string', 'max' => 32],
            [['barcode', 'logistics_id'], 'string', 'max' => 30],
            [['brief'], 'string', 'max' => 255],
            [['image_id'], 'string', 'max' => 64],
            [['ship_address'], 'string', 'max' => 128],
            [['ship_name', 'ship_phone'], 'string', 'max' => 16],
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
            'ship_area_id' => 'Ship Area ID',
            'ship_address' => 'Ship Address',
            'ship_name' => 'Ship Name',
            'ship_phone' => 'Ship Phone',
            'logistics_name' => 'Logistics Name',
            'logistics_id' => 'Logistics ID',
            'is_comment' => 'Is Comment',
            'comment_at' => 'Comment At',
            'is_receive' => 'Is Receive',
            'receive_at' => 'Receive At',
            'is_exchange' => 'Is Exchange',
            'exchange_at' => 'Exchange At',
            'is_return' => 'Is Return',
            'return_at' => 'Return At',
            'confirm' => 'Confirm',
            'confirm_at' => 'Confirm At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(),['id'=>'order_id'])->select(['id','status','lat','lon']);
    }

    public function getFile()
    {
        return $this->hasOne(File::className(),['id'=>'image_id']);
    }
}
