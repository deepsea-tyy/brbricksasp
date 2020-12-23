<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;
use bricksasp\spu\models\Goods;
use bricksasp\spu\models\GoodsProduct;
use bricksasp\promotion\models\Promotion;
use bricksasp\promotion\models\PromotionCoupon;
use bricksasp\promotion\models\PromotionCondition;
use bricksasp\models\ShoppingCart;

/**
 * This is the model class for table "{{%order}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property float|null $total_price 订单总价
 * @property float|null $pay_price 支付价格
 * @property int|null $pay_status 支付状态1未付款2已付款3部分付款4部分退款5已退款
 * @property float|null $payed_price 已付金额
 * @property string|null $pay_platform 支付方式 2微信3支付宝
 * @property int|null $pay_at 支付时间
 * @property string|null $logistics_name 配送方式名称
 * @property float|null $logistics_price 配送费用
 * @property string|null $logistics_id 物流号
 * @property int|null $seller_id 店铺id
 * @property int|null $confirm 确单状态0未确认收货1确认收货
 * @property int|null $confirm_at 确认收货时间
 * @property int|null $store_id 自提门店ID
 * @property int|null $ship_status 发货状态1未发货2已发货3部分发货4部分退货5已退货
 * @property int|null $ship_area_id 收货地区ID
 * @property string|null $ship_address 收货详细地址
 * @property string|null $ship_name 收货人姓名
 * @property string|null $ship_phone 收货电话
 * @property float|null $total_weight 商品总重量
 * @property float|null $total_volume 商品总体积
 * @property int|null $tax_type 是否开发票1个人发票2公司发票
 * @property string|null $tax_content 发票内容
 * @property int|null $type 订单类型1默认2其他订单
 * @property string|null $tax_code 税号
 * @property string|null $tax_title 发票抬头
 * @property int|null $point 使用积分
 * @property float|null $point_money 积分抵扣
 * @property string|null $promotion_info 优惠信息
 * @property float|null $order_pmt 订单优惠金额
 * @property string|null $coupon 优惠券信息
 * @property string|null $memo 买家备注
 * @property string|null $ip 下单IP
 * @property string|null $mark 卖家备注
 * @property int|null $source 订单来源1pc 2wechat
 * @property int|null $status 1正常2完成3取消4删除
 * @property int|null $is_comment 1已评论
 * @property int|null $is_delete
 * @property string|null $lat
 * @property string|null $lon
 * @property int|null $created_at 创建时间
 * @property int|null $updated_at 更新时间
 */
class Order extends \bricksasp\base\BaseActiveRecord
{
    const PLATFORM_WX = 2; // 微信
    const PLATFORM_ALI = 3; // 支付宝

    const TYPE_DEFAULT = 1; // 默认类型
    const TYPE_OTHER = 2; // 其他订单

    const SHIP_STATUS_NO = 1; //未发货
    const SHIP_STATUS_YES = 2; //已发货
    const CONFIRM_NO = 1; //未确认收货

    const STATUS_NORMAL = 1; //订单状态 正常
    const STATUS_COMPLETE = 2; //订单状态 完成
    const STATUS_CANCEL = 3; //订单状态 取消
    const STATUS_DELETE = 3; //订单状态 取消

    const PAY_NO = 1; // 未付款
    const PAY_ALL = 2; // 已付款
    const PAY_PART = 3; // 部分付款 
    const PAY_REFUND_PART = 4; // 部分退款 
    const PAY_REFUND = 5; // 已退款

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => \bricksasp\common\SnBehavior::className(),
                'attribute' => 'id',
                'type' => \bricksasp\common\SnBehavior::SN_ORDER,
            ],
            \yii\behaviors\TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'owner_id', 'user_id', 'pay_status', 'pay_at', 'seller_id', 'confirm', 'confirm_at', 'store_id', 'ship_status', 'ship_area_id', 'tax_type', 'type', 'point', 'source', 'status', 'is_comment', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['total_price', 'pay_price', 'payed_price', 'logistics_price', 'total_weight', 'total_volume', 'point_money', 'order_pmt'], 'number'],
            [['pay_platform'], 'string', 'max' => 8],
            [['logistics_name'], 'string', 'max' => 32],
            [['logistics_id'], 'string', 'max' => 30],
            [['ship_address'], 'string', 'max' => 200],
            [['ship_name', 'tax_title'], 'string', 'max' => 50],
            [['ship_phone', 'lat', 'lon'], 'string', 'max' => 16],
            [['tax_content', 'promotion_info', 'coupon', 'memo', 'mark'], 'string', 'max' => 255],
            [['tax_code', 'ip'], 'string', 'max' => 64],
            [['id'], 'unique'],


            [['pay_status', 'ship_status', 'status', 'type', 'source'], 'default', 'value' => static::TYPE_DEFAULT],
            [['confirm', 'point', 'total_price', 'pay_price', 'payed_price', 'logistics_price', 'total_weight', 'total_volume', 'point_money', 'order_pmt'], 'default', 'value' => 0],
            [['ip'], 'default', 'value' => Tools::client_ip()]
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
            'total_price' => 'Total Price',
            'pay_price' => 'Pay Price',
            'pay_status' => 'Pay Status',
            'payed_price' => 'Payed Price',
            'pay_platform' => 'Pay Platform',
            'pay_at' => 'Pay At',
            'logistics_name' => 'Logistics Name',
            'logistics_price' => 'Logistics Price',
            'logistics_id' => 'Logistics ID',
            'seller_id' => 'Seller ID',
            'confirm' => 'Confirm',
            'confirm_at' => 'Confirm At',
            'store_id' => 'Store ID',
            'ship_status' => 'Ship Status',
            'ship_area_id' => 'Ship Area ID',
            'ship_address' => 'Ship Address',
            'ship_name' => 'Ship Name',
            'ship_phone' => 'Ship Phone',
            'total_weight' => 'Total Weight',
            'total_volume' => 'Total Volume',
            'tax_type' => 'Tax Type',
            'tax_content' => 'Tax Content',
            'type' => 'Type',
            'tax_code' => 'Tax Code',
            'tax_title' => 'Tax Title',
            'point' => 'Point',
            'point_money' => 'Point Money',
            'promotion_info' => 'Promotion Info',
            'order_pmt' => 'Order Pmt',
            'coupon' => 'Coupon',
            'memo' => 'Memo',
            'ip' => 'Ip',
            'mark' => 'Mark',
            'source' => 'Source',
            'status' => 'Status',
            'is_comment' => 'Is Comment',
            'is_delete' => 'Is Delete',
            'lat' => 'Lat',
            'lon' => 'Lon',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getOrderItem()
    {
        return $this->hasMany(OrderItem::className(),['order_id'=>'id']);
    }

    public function saveData($data)
    {
        list($data, $orderItems, $purchase) = $this->formatData($data);
        // print_r($data);exit;

        $transaction = self::getDb()->beginTransaction();
        try {
            $this->load($data);
            $this->save();
            if (!$this->id) {
                $transaction->rollBack();
                Tools::breakOff('下单失败,请重试');
            }
            foreach ($orderItems as $k => $v) {
                $v['order_id'] = $this->id;
                $orderItems[$k] = $v;
                Goods::updateAllCounters(['sell_num'=>$v['num'],'buy_num'=>$v['num']],['id'=>$v['goods_id']]);
            }
            foreach ($purchase as $v) {
                GoodsProduct::updateAllCounters(['stock'=> -$v['num']], ['id' => $v['id']]);
            }

            Yii::$app->db->createCommand()->batchInsert(OrderItem::tableName(), array_keys(end($orderItems)), $orderItems)->execute();
            
            if (!empty($data['cart_ids'])) {
                ShoppingCart::deleteAll(['id'=>$data['cart_ids']]);
            }

            $transaction->commit();
            return true;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            Tools::breakOff($e->getMessage());
        }
        return $this->save();
    }

    public function formatData($data)
    {
        $data = parent::formatData($data);
        if (empty($data['cart_ids'])) {
            $pids = [$data['buy_now']['product_id']];
            $nums = [$data['buy_now']['product_id'] => $data['buy_now']['num']];
        }else{
            $carts = ShoppingCart::find()->where(['id'=>$data['cart_ids']])->asArray()->all();
            $pids = array_column($carts,'product_id');
            $nums = array_column($carts, 'num', 'product_id');
        }
        if (!array_filter($pids)) {
            Tools::breakOff(950001);
        }

        // 收货地址
        $shipAdr = ShipAddress::find()->where(['id' => $data['ship_id']??0])->one();
        if ($shipAdr) {
            $data['ship_area_id'] = $shipAdr->area_id;
            $data['ship_address'] = $shipAdr->address;
            $data['ship_name'] = $shipAdr->name;
            $data['ship_phone'] = $shipAdr->phone;
        }

        $products = GoodsProduct::find()->with(['goods'])->where(['id' => $pids])->all();
        
        $purchase=$orderItems=[];// 下单减库存 订单详情
        if (empty($products)) {
            Tools::breakOff('单品无效');
        }

        // 优惠券处理 达成条件,促销 暂未实现
        $coupon=$useCoupon=[];
        if (!empty($data['coupon_ids'])) {
            $model = new PromotionCoupon();
            $coupon =  $model->checkEffectiveness($data['coupon_ids'],$data['owner_id']);
        }

        $user = UserInfo::findOne($data['user_id']);
        $vip = UserVip::findOne($user->vip);
        $t = time();
        //价格计算
        $orderCp=null;
        foreach($products as $k=>$v){
            if($nums[$v['id']] < 1){
                Tools::breakOff('商品购买数量小于1');
            }

            if ($v->goods->check_stock == Goods::TYPE_CHECK_STOCK_ORDER) { // 下单减库存
                $purchase[] = ['id'=>$v->id, 'num'=>$nums[$v->id]];
            }
            if(($v['stock'] - $v['freeze_stock']) < 1){
                Tools::breakOff('库存不足');
            }

            if($v->goods->on_shelves != Goods::ONSHELVES_ON || $v->on_shelves != Goods::ONSHELVES_ON){
                Tools::breakOff('该商品已下架');
            }

            // 会员价计算
            if ($v->is_vip_discount && $vip && $vip->status && $user->vip_duration > $t && (empty($v->vip) || ((int)$vip->level && $vip->level >= $v->vip))) {
                $item['pay_price']  = $v->price * $vip->discount * $nums[$v->id] / 10;
            }else{
                $item['pay_price']  = $v->price;
            }
            $item['user_id']   = $data['user_id'];
            $item['owner_id']   = $data['owner_id'];
            $item['goods_id']   = $v->goods->id;
            $item['goods_id']   = $v->goods->id;
            $item['product_id'] = $v->id;
            $item['name']       = $v->name;
            $item['barcode']    = $v->barcode;
            $item['brief']      = $v->goods->brief;
            $item['price']      = $v->price;
            $item['costprice']  = $v->costprice;
            $item['mktprice']   = $v->mktprice;
            $item['image_id']   = $v->goods->image_id;
            $item['num']        = $nums[$v->id];
            $item['weight']     = $v->weight * $nums[$v->id];
            $item['volume']     = $v->volume * $nums[$v->id];
            $item['created_at'] = $t;
            $item['updated_at'] = $t;
            $orderItems[$v->id] = $item;

            $itemCp=null;
            // 促销价计算 优惠券
            if ($coupon) {
                foreach ($coupon as $k2 => $cp) {//找券
                    if ($cp->condition->content_type == PromotionCondition::CONTENT_ACHIEVE) {
                        $orderCp = $cp->condition;
                        unset($coupon[$k2]);
                        break;
                    }
                    if ($cp->condition->content_type == PromotionCondition::CONTENT_CAT && $v->goods->cat_id == $cp->condition->content) {
                        $itemCp = $cp->condition;
                    }
                    if ($cp->condition->content_type == PromotionCondition::CONTENT_GOODS && $v->goods->id == $cp->condition->content) {
                        $itemCp = $cp->condition;
                    }
                    if ($cp->condition->content_type == PromotionCondition::CONTENT_PRODUCT && $v->id == $cp->condition->content) {
                        $itemCp = $cp->condition;
                    }
                    if ($itemCp) {
                        if ($itemCp->result_type == PromotionCondition::RESULT_ORDER_FIX_REDUCE || $itemCp->result_type == PromotionCondition::RESULT_ORDER_DISCOUNT || $itemCp->result_type == PromotionCondition::RESULT_ORDER_ONE_PRICE) {
                            $orderCp = $itemCp;
                            $itemCp=null;
                        }
                        unset($coupon[$k2]);
                        break;
                    }
                }
                if ($itemCp) {
                    if ($itemCp->result_type == PromotionCondition::RESULT_FIX_REDUCE) {
                        $orderItems[$v->id]['pay_price'] = $orderItems[$v->id]['pay_price'] - $itemCp->result;
                    }
                    if ($itemCp->result_type == PromotionCondition::RESULT_DISCOUNT) {
                        $orderItems[$v->id]['pay_price'] = $orderItems[$v->id]['pay_price'] * $itemCp->result / 10;
                    }
                    if ($itemCp->result_type == PromotionCondition::RESULT_ONE_PRICE) {
                        $orderItems[$v->id]['pay_price'] = $itemCp->result;
                    }
                }
                if ($orderItems[$v->id]['pay_price'] <  0) {
                    $orderItems[$v->id]['pay_price'] = 0;
                }
                $orderItems[$v->id]['pmt_price'] = $orderItems[$v->id]['price'] - $orderItems[$v->id]['pay_price'];
            }

            $data['total_price'] = $data['total_price']??0 + $orderItems[$v->id]['price'];
            $data['pay_price'] = $data['pay_price']??0 + $orderItems[$v->id]['pmt_price'];
            
            if ($orderCp || $itemCp) {
                $useCoupon[] = $orderCp? $orderCp->toArray() : $itemCp->toArray();
            }
        }
        // 订单价计算
        if ($orderCp) {
            if ($orderCp->result_type == PromotionCondition::RESULT_ORDER_FIX_REDUCE) {
                $data['pay_price'] = $data['pay_price'] - $orderCp->result;
            }
            if ($orderCp->result_type == PromotionCondition::RESULT_ORDER_DISCOUNT) {
                $data['pay_price'] = $data['pay_price'] * $orderCp->result / 10;
            }
            if ($orderCp->result_type == PromotionCondition::RESULT_ORDER_ONE_PRICE) {
                $data['pay_price'] = $orderCp->result;
            }
        }

        if ($useCoupon) {// 优惠信息
            $data['promotion_info'] = json_encode($useCoupon);
        }
        if ($data['total_price'] < 0) {
            $data['total_price'] = 0;
        }
        $data['order_pmt'] = $data['total_price'] - $data['pay_price'];
        $data['created_at'] = $t;
        $data['updated_at'] = $t;
        $data['seller_id'] = $data['owner_id'];

        // print_r($data);exit();
        return [$data, $orderItems, $purchase];
    }


}
