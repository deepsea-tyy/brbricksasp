<?php

namespace bricksasp\shop\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\OrderItem;
use bricksasp\models\ShipAddress;
use bricksasp\models\redis\Token;
use bricksasp\base\BackendController;
use bricksasp\models\form\OrderValidate;
use yii\data\ActiveDataProvider;

/**
 * OrderController implements the CRUD actions for OrderItem model.
 */
class OrderController extends BackendController
{
    /**
     * @OA\Get(path="/shop/order/index",
     *   summary="商铺订单列表",
     *   tags={"shop模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
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
        $query =  OrderItem::find();
        $query->andFilterWhere([
            'status' => $params['status']??null,
        ]);
        $query->andFilterWhere($this->ownerCondition());
        $query->orFilterWhere(['like', 'ship_phone', $params['ship_phone']??null]);
        $query->orFilterWhere(['like', 'id', $params['id']??null]);
        $query->with(['order','file']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $list = [];
        foreach ($dataProvider->models as $item) {
        	$row = $item->toArray();
        	$row['order'] = $item->order;
        	$row['file'] = $item->file;
        	$list[] = $row;
        }

        return $this->success([
          'list' => $list,
          'pageCount' => $dataProvider->pagination->pageCount,
          'totalCount' => $dataProvider->pagination->totalCount,
          'page' => $dataProvider->pagination->page + 1,
          'pageSize' => $dataProvider->pagination->limit,
        ]);
    }

    /**
     * @OA\Get(path="/shop/order/view",
     *   summary="商铺订单详情",
     *   tags={"shop模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   @OA\Parameter(name="id",in="query",required=true,@OA\Schema(type="integer"),description="订单id"),
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/OrderItemView"
     *       ),
     *     ),
     *   ),
     * )
     *
     * 
     * 
     * @OA\Schema(
     *   schema="OrderItemView",
     *   title="商铺订单详情数据结构",
     *   @OA\Property(property="id",type="integer",description="id"),
     *   @OA\Property(property="order_id",type="integer",description="订单id"),
     *   @OA\Property(property="goods_id",type="integer",description="商品id"),
     *   @OA\Property(property="product_id",type="integer",description="单品id"),
     *   @OA\Property(property="name",type="string",description="商品名称"),
     *   @OA\Property(property="barcode",type="string",description="商品条码"),
     *   @OA\Property(property="brief",type="string",description="商品简介"),
     *   @OA\Property(property="price",type="number",description="售价"),
     *   @OA\Property(property="costprice",type="number",description="单品成本价单价"),
     *   @OA\Property(property="mktprice",type="number",description="单品市场价"),
     *   @OA\Property(property="image_id",type="string",description="图片id"),
     *   @OA\Property(property="num",type="number",description="数量"),
     *   @OA\Property(property="pay_price",type="number",description="支付总金额"),
     *   @OA\Property(property="pmt_price",type="number",description="优惠总金额"),
     *   @OA\Property(property="weight",type="string",description="总重量"),
     *   @OA\Property(property="volume",type="string",description="总体积"),
     *   @OA\Property(property="delivery_num",type="number",description="交货数量"),
     *   @OA\Property(property="ship_area_id",type="number",description="收货地区ID"),
     *   @OA\Property(property="ship_address",type="number",description="收货详细地址"),
     *   @OA\Property(property="ship_name",type="number",description="收货人姓名"),
     *   @OA\Property(property="ship_phone",type="number",description="收货电话"),
     *   @OA\Property(property="logistics_name",type="number",description="配送方式名称"),
     *   @OA\Property(property="logistics_id",type="number",description="物流号"),
     *   @OA\Property(property="is_comment",type="integer",description="1已评论"),
     *   @OA\Property(property="comment_at",type="integer",description="评论时间"),
     *   @OA\Property(property="is_receive",type="integer",description="1已收货"),
     *   @OA\Property(property="receive_at",type="integer",description="时间"),
     *   @OA\Property(property="is_exchange",type="integer",description="1已换货"),
     *   @OA\Property(property="exchange_at",type="integer",description="时间"),
     *   @OA\Property(property="is_return",type="integer",description="1已退货"),
     *   @OA\Property(property="return_at",type="integer",description="时间"),
     *   @OA\Property(property="confirm",type="integer",description="确单状态1确认"),
     *   @OA\Property(property="confirm_at",type="integer",description="确认时间"),
     *   @OA\Property(property="order",type="object",description="",
     *     @OA\Property(property="status",type="integer",description="1正常2完成3取消4删除"),
     *     @OA\Property(property="lat",type="string",description=""),
     *     @OA\Property(property="lon",type="string",description=""),
     *   ),
     * )
     */
	public function actionView() {
        $model = $this->findModel(Yii::$app->request->get('id'));
        $data = $model->toArray();
        $data['orderItem'] = $model->orderItem;
        $data['goods'] = $model->goods;
        $data['product'] = $model->product;
        return $this->success($data);
	}

    /**
     * @OA\Post(path="/shop/order/update",
     *   summary="修改商铺订单",
     *   tags={"shop模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/OrderItemUpdate"
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
     *   schema="OrderItemUpdate",
     *   description="商铺订单数据",
     *   @OA\Property(property="id", type="integer", description="id"),
     *   @OA\Property(property="ship_area_id",type="integer",description="收货地区ID"),
     *   @OA\Property(property="ship_address",type="string",description="收货详细地址"),
     *   @OA\Property(property="ship_name",type="string",description="收货人姓名"),
     *   @OA\Property(property="ship_phone",type="string",description="收货电话"),
     *   @OA\Property(property="confirm",type="integer",description="确单状态1确认"),
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel($this->updateCondition(['id'=>$params['id'] ?? 0]));

        $upField = ['ship_area_id','ship_address','ship_name','ship_phone','confirm','confirm_at'];

        if (!empty($params['confirm'])) {
            $params['confirm_at'] = time();
        }
        
        if ($model->load($params) && $model->save(true,$upField)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     *
     * @OA\Post(path="/shop/order/delete",
     *   summary="删除商铺订单",
     *   tags={"shop模块"},
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
	 * Finds the OrderItem model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param string $id
	 * @return OrderItem the loaded model
	 */
	protected function findModel($id) {
        if (($model = OrderItem::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
	}
}
