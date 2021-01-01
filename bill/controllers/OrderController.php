<?php
namespace bricksasp\bill\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\Order;
use bricksasp\models\OrderItem;
use bricksasp\models\PlaceOrder;
use bricksasp\models\ShipAddress;
use bricksasp\models\redis\Token;
use bricksasp\base\BackendController;
use bricksasp\models\form\OrderValidate;
use yii\data\ActiveDataProvider;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends BackendController
{
    /**
     * @OA\Get(path="/bill/order/index",
     *   summary="订单列表",
     *   tags={"bill模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
     *   @OA\Parameter(name="is_delete",in="query",@OA\Schema(type="integer"),description="1软删除"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/pagination"),
     *     ),
     *   ),
     * )
     */
	public function actionIndex() {
        $params = Yii::$app->request->get();
        $query =  Order::find();
        $query->andFilterWhere([
            'status' => $params['status']??null,
        ]);
        $query->andFilterWhere($this->ownerCondition());
        $query->andFilterWhere(['is_delete'=> empty($params['is_delete']) ? 0 : 1]);
        $query->orFilterWhere(['like', 'ship_phone', $params['ship_phone']??null]);
        $query->orFilterWhere(['like', 'id', $params['id']??null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $this->success([
          'list' => $dataProvider->models,
          'pageCount' => $dataProvider->pagination->pageCount,
          'totalCount' => $dataProvider->pagination->totalCount,
          'page' => $dataProvider->pagination->page + 1,
          'pageSize' => $dataProvider->pagination->limit,
        ]);
    }

    /**
     * @OA\Get(path="/bill/order/view",
     *   summary="订单详情",
     *   tags={"bill模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   @OA\Parameter(name="id",in="query",required=true,@OA\Schema(type="integer"),description="订单id"),
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/OrderView"
     *       ),
     *     ),
     *   ),
     * )
     *
     * 
     * 
     * @OA\Schema(
     *   schema="OrderView",
     *   title="订单详情数据结构",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="订单号"),
     *       @OA\Property(property="total_price", type="number", description="订单总价"),
     *       @OA\Property(property="pay_price", type="number", description="支付价格"),
     *       @OA\Property(property="pay_status", type="integer", description="支付状态1未付款2已付款3部分付款4部分退款5已退款" ),
     *       @OA\Property(property="payed_price", type="number", description="已付金额" ),
     *       @OA\Property(property="pay_platform", type="string", description="支付方式 2微信用户3支付宝用户"),
     *       
     *       @OA\Property(property="pay_at", type="integer", description="支付时间"),
     *       @OA\Property(property="logistics_name", type="string", description="配送方式名称"),
     *       @OA\Property(property="logistics_price",type="number",description="配送费用"),
     *       @OA\Property(property="logistics_id",type="string",description="物流号"),
     *       
     *       @OA\Property(property="seller_id",type="string",description="店铺id"),
     *       @OA\Property(property="complete",type="integer",description="确单状态0未确认收货1确认收货"),
     *       @OA\Property(property="complete_at",type="integer",description="确认收货时间"),
     *       @OA\Property(property="confirm",type="integer",description="确单状态0未确认确单1确认确单"),
     *       @OA\Property(property="confirm_at",type="integer",description="确认确单时间"),
     *       @OA\Property(property="store_id",type="integer",description="自提门店ID"),
     *       
     *       @OA\Property(property="ship_status",type="integer",description="发货状态1未发货2已发货3部分发货4部分退货5已退货"),
     *       @OA\Property(property="ship_area_id",type="integer",description="收货地区ID"),
     *       @OA\Property(property="ship_address",type="string",description="收货详细地址"),
     *       @OA\Property(property="ship_name",type="string",description="收货人姓名"),
     *       @OA\Property(property="ship_phone",type="string",description="收货电话"),
     *       
     *       @OA\Property(property="total_weight",type="number",description="商品总重量"),
     *       @OA\Property(property="total_volume",type="number",description="商品总体积"),
     *       @OA\Property(property="tax_type",type="integer",description="是否开发票1个人发票2公司发票"),
     *       @OA\Property(property="tax_content",type="string",description="发票内容"),
     *       @OA\Property(property="type",type="integer",description="订单类型1默认2其他订单"),
     *       @OA\Property(property="tax_code",type="string",description="税号"),
     *       @OA\Property(property="tax_title",type="string",description="发票抬头"),
     *       @OA\Property(property="point",type="integer",description="使用积分"),
     *       @OA\Property(property="point_money",type="number",description="积分抵扣"),
     *       @OA\Property(property="promotion_info",type="string",description="优惠信息"),
     *       @OA\Property(property="order_pmt",type="number",description="订单优惠金额"),
     *       @OA\Property(property="coupon",type="string",description="优惠券信息"),
     *       @OA\Property(property="memo",type="string",description="买家备注"),
     *       @OA\Property(property="ip",type="string",description="下单IP"),
     *       @OA\Property(property="mark",type="string",description="卖家备注"),
     *       @OA\Property(property="source",type="integer",description="订单来源1pc 2wechat"),
     *       @OA\Property(property="status",type="integer",description="1正常2完成3取消4删除"),
     *       @OA\Property(property="is_comment",type="integer",description="1已评论"),
     *       @OA\Property(property="lat",type="string",description=""),
     *       @OA\Property(property="lon",type="string",description=""),
     *     ),
     *     @OA\Schema(
     *       @OA\Property(property="orderItem",type="array",@OA\Items(
     *           @OA\Property(property="id",type="integer",description="id"),
     *           @OA\Property(property="order_id",type="integer",description="订单id"),
     *           @OA\Property(property="goods_id",type="integer",description="商品id"),
     *           @OA\Property(property="product_id",type="integer",description="单品id"),
     *           @OA\Property(property="name",type="string",description="商品名称"),
     *           @OA\Property(property="barcode",type="string",description="商品条码"),
     *           @OA\Property(property="brief",type="string",description="商品简介"),
     *           @OA\Property(property="price",type="number",description="售价"),
     *           @OA\Property(property="costprice",type="number",description="单品成本价单价"),
     *           @OA\Property(property="mktprice",type="number",description="单品市场价"),
     *           @OA\Property(property="image_id",type="string",description="图片id"),
     *           @OA\Property(property="num",type="number",description="数量"),
     *           @OA\Property(property="pay_price",type="number",description="支付总金额"),
     *           @OA\Property(property="pmt_price",type="number",description="优惠总金额"),
     *           @OA\Property(property="weight",type="string",description="总重量"),
     *           @OA\Property(property="volume",type="string",description="总体积"),
     *           @OA\Property(property="delivery_num",type="number",description="交货数量"),
     *           @OA\Property(property="orderItemStatus",type="array",@OA\Items(
     *             @OA\Property(property="order_id",type="integer",description="订单id"),
     *             @OA\Property(property="item_id",type="integer",description="item_id"),
     *             @OA\Property(property="is_comment",type="integer",description="1已评论"),
     *             @OA\Property(property="comment_at",type="integer",description="评论时间"),
     *             @OA\Property(property="is_receive",type="integer",description="1已收货"),
     *             @OA\Property(property="receive_at",type="integer",description="时间"),
     *             @OA\Property(property="is_exchange",type="integer",description="1已换货"),
     *             @OA\Property(property="exchange_at",type="integer",description="时间"),
     *             @OA\Property(property="is_return",type="integer",description="1已退货"),
     *             @OA\Property(property="return_at",type="integer",description="时间"),
     *           )),
     *         ),
     *         description="单品列表"
     *       ),
     *     )
     *   }
     * )
     */
	public function actionView() {
        $model = $this->findModel(Yii::$app->request->get('id'));
        $data = $model->toArray();
        $data['orderItem'] = $model->orderItem;
        $data['goods'] = $model->goods;
        $data['product'] = $model->product;
        $data['promotion_info'] = json_decode($model->promotion_info);
        return $this->success($data);
	}

    /**
     * @OA\Post(path="/bill/order/create",
     *   summary="统一下单接口",
     *   description="说明：购物车参数与单品参数二选一，购物车参数优先",
     *   tags={"bill模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/OrderCreate"
     *       )
     *     )
     *   ),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     *
     * 
     * @OA\Schema(
     *   schema="OrderCreate",
     *   title="下单数据结构",
     *   @OA\Property(property="cart_ids",type="array",description="购物车ids", @OA\Items(example=1)),
     *   @OA\Property(property="buy_now",type="object",description="单品立即购买", 
     *     @OA\Property(property="product_id",type="integer",description="单品id",),
     *     @OA\Property(property="num",type="integer",description="单品数量",example=1),
     *   ),
     *   @OA\Property(property="coupon_ids",type="array",description="优惠券", @OA\Items(example=1)),
     *   @OA\Property(property="show_id",type="integer",description="会员数字码",),
     *   @OA\Property(property="ship_id",type="integer",description="收货地址id",),
     *   @OA\Property(property="pay_now",type="integer",description="立即支付 返回支付参数 1是 0否",example="0"),
     *   @OA\Property(property="store_id",type="integer",description="自提门店id 表示线下自提",),
     *   @OA\Property(property="pay_platform",type="integer",example="2",description="支付方式 2微信3支付宝",),
     *   @OA\Property(property="pay_type",type="string",example="qr",description="支付类型 (查看获取支付参数接口)",),
     * )
     */
	public function actionCreate() {
		$params = $this->queryMapPost();
		$validator = new OrderValidate($params, ['scenario' => 'create_order']);
		if ($validator->validate()) {
			$model = new Order();
			if ($model->saveData($this->sysParams($validator->getSaveData()))) {
                $data = $model->toArray();
    			if (!empty($params['pay_now']) && $params['pay_now'] == 1) {
    				$params['order_id'] = $model->id;
    				$vtro = new OrderValidate($params, ['scenario' => 'create_bill']);
    				if ($vtro->validate()) {
    					$payData['order_id'] = $model->id;
    					$payData['money'] = $model->pay_amount;
    					$payData['owner_id'] = $params['owner_id'];
    					$payData['user_id'] = $params['user_id'];
    					$payData['orderItems'] = $model->orderItems;
    					$res = PlaceOrder::newBill(ucfirst(str_replace('pay', '', $params['payment_code'])), $params['payment_type'], $payData);
    					$res['order_id'] = $model->id;
    					return $res ? $this->success($res) : $this->success($data,PlaceOrder::$error);
    				}
    				return $this->success($data, $vtro->errors);
    			}
                return $this->success($data);
            }
            return $this->fail($model->errors);
		}

		return $this->fail($validator->errors);
	}

    /**
     * @OA\Post(path="/bill/order/update",
     *   summary="修改订单",
     *   tags={"bill模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/OrderUpdate"
     *       )
     *     )
     *   ),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     * 
     * 
     * @OA\Schema(
     *   schema="OrderUpdate",
     *   description="订单数据",
     *   @OA\Property(property="id", type="integer", description="id"),
     *   @OA\Property(property="ship_id",type="integer",description="用户选择收货地址id",),
     *   @OA\Property(property="ship_area_id",type="integer",description="收货地区ID"),
     *   @OA\Property(property="ship_address",type="string",description="收货详细地址"),
     *   @OA\Property(property="ship_name",type="string",description="收货人姓名"),
     *   @OA\Property(property="ship_phone",type="string",description="收货电话"),
     *   @OA\Property(property="status",type="integer",description="1正常2完成3取消4删除"),
     *   @OA\Property(property="memo",type="string",description="买家备注"),
     *   @OA\Property(property="complete",type="integer",description="确单状态0未确认收货1确认收货"),
     *   @OA\Property(property="complete_at",type="integer",description="确认收货时间"),
     *   @OA\Property(property="confirm",type="integer",description="确单状态0未确认确单1确认确单"),
     *   @OA\Property(property="confirm_at",type="integer",description="确认确单时间"),
     *   @OA\Property(property="pay_price", type="number", description="支付价格"),
     *   @OA\Property(property="pay_status", type="integer", description="支付状态1未付款2已付款3部分付款4部分退款5已退款" ),
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel($this->updateCondition(['id'=>$params['id'] ?? 0]));

        $upField = ['ship_area_id','ship_address','ship_name','ship_phone','status','memo','complete','complete_at','confirm','confirm_at'];
        if ($this->current_login_type == Token::TOKEN_TYPE_BACKEND) {
            array_push($upField,'pay_price','pay_status');
        }

        if (!empty($params['ship_id'])) {
            $shipAdr = ShipAddress::find()->where(['user_id'=>$this->current_user_id, 'id'=>$params['ship_id']])->one();
            if (empty($shipAdr)) {
                Tools::breakOff('收货地址无效');
            }

            $params['ship_area_id'] = $shipAdr->area_id;
            $params['ship_address'] = $shipAdr->address;
            $params['ship_name'] = $shipAdr->name;
            $params['ship_phone'] = $shipAdr->phone;
        }
        if (!empty($params['confirm'])) {
            $params['confirm_at'] = time();
        }
        if (!empty($params['complete'])) {
            $params['complete_at'] = time();
        }
        
        if ($model->load($params) && $model->save(true,$upField)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     *
     * @OA\Post(path="/bill/order/delete",
     *   summary="删除订单",
     *   tags={"bill模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="ids", type="array", description="ids", @OA\Items()),
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     */
    public function actionDelete()
    {
        $params = $this->queryMapPost();
        if (Order::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }
        OrderItem::deleteAll(['order_id'=>$params['ids']??0]);
        return Order::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }

    /**
     *
     * @OA\Post(path="/bill/order/delivery",
     *   summary="抢单送货",
     *   tags={"bill模块"},
     *   @OA\Parameter(name="X-Token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="order_id",type="integer",description="订单id")
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     */
    public function actionDelivery() {
        $params = $this->queryMapPost();
		$order_id = $params['order_id'];
		$key = 'graborder' . $order_id;
		if (Yii::$app->redis->setnx($key,1)) {
			try {
				$documentary = OrderDocumentary::find()->where(['order_id'=>$order_id, 'type' => OrderDocumentary::TYPE_GRABORDER])->one();
				if ($documentary) {
					return $this->fail('差一点点运气，该单已被抢走～～');
				}
				$order = Order::find()->select(['owner_id'])->where(['id'=>$order_id])->one();
				if (empty($order)) {
					return $this->fail('订单号无效');
				}
				$model = new OrderDocumentary();
				$params['owner_id'] = $order->owner_id;
				$model->load($params);
				if ($model->save()) {
					Yii::$app->redis->del($key);
					return $this->success();
				}
			} catch (Throwable $e) {
				Yii::$app->redis->del($key);
				return $this->fail('请重试');
			}
		}
		Yii::$app->redis->del($key);
        return $this->fail('差一点点运气，该单已被抢走～～');
    }

	/**
	 * Finds the Order model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param string $id
	 * @return Order the loaded model
	 */
	protected function findModel($id) {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
	}
}
