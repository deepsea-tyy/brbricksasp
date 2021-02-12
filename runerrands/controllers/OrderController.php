<?php

namespace bricksasp\runerrands\controllers;

use Yii;
use bricksasp\base\Tools;
use yii\data\ActiveDataProvider;
use bricksasp\models\Order;
use bricksasp\models\OrderRunerrands;

class OrderController extends \bricksasp\base\BackendController
{
    public function loginAction()
    {
        return [
            'index',
            'view',
            'create',
            'update',
            'delete',
        ];
    }

    /**
     * @OA\Get(path="/runerrands/order/index",
     *   summary="跑腿订单列表",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
     *   @OA\Parameter(name="complete",in="query",@OA\Schema(type="integer"),description="完成"),
     *   @OA\Parameter(name="pay_status",in="query",@OA\Schema(type="integer"),description="支付状态"),
     *   @OA\Parameter(name="receiver",in="query",@OA\Schema(type="integer"),description="接单人"),
     *   @OA\Parameter(name="delivery",in="query",@OA\Schema(type="integer"),description="接单列表"),
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
    public function actionIndex()
    {
        $params = Yii::$app->request->get();
        $query = Order::find()->with(['runerrands']);
        $query->andFilterWhere($this->updateCondition(['type'=>[2,3,4,5]]));
        $query->andFilterWhere(['complete'=>$params['complete']??null]);
        $query->andFilterWhere(['pay_status'=>$params['pay_status']??null]);
        $query->orderBy('created_at desc');
        if (!empty($params['delivery'])) {
            $query->andWhere(['pay_status'=>Order::PAY_ALL, 'receiver'=>null, 'status'=>Order::STATUS_NORMAL]);
        }
        if (!empty($params['pay_status']) && $params['pay_status'] == Order::PAY_ALL && !empty($params['receiver'])) {//代接单
            $query->andFilterWhere(['not', ['receiver' => null]]);
        }else{
            $query->andFilterWhere(['receiver'=>empty($params['receiver'])?null:$this->current_user_id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $list=[];
        foreach ($dataProvider->models as $item) {
            $row = $item->toArray();
            $row['runerrands'] = $item->runerrands;
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
     * @OA\Get(path="/runerrands/order/view",
     *   summary="跑腿订单详情",
     *   tags={"跑腿模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="id",),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       
     *       @OA\Schema(ref="#/components/schemas/OrderRunerrandsUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel($this->updateCondition(empty($params['id']) ? [] : ['id'=>$params['id']]));
        
        $data = $model->toArray();
        $data['runerrands'] = $model->runerrands;
        $data['shipAddress'] = $model->shipAddress;
        $data['rider'] = $model->rider;

        return $this->success($data);
    }

    /**
     * @OA\Post(path="/runerrands/order/create",
     *   summary="创建跑腿订单",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/OrderRunerrandsCreate"
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
     * @OA\Schema(
     *   schema="OrderRunerrandsCreate",
     *   description="跑腿订单",
     *   @OA\Property(property="content", type="string", description="办事内容"),
     *   @OA\Property(property="start_place", type="string", description="起始地"),
     *   @OA\Property(property="end_place", type="string", description="目的地",),
     *   @OA\Property(property="time", type="string", description="办事时间",),
     *   @OA\Property(property="weight", type="integer", description="重量",),
     *   @OA\Property(property="gender", type="integer", description="0女1男",),
     *   @OA\Property(property="overtime", type="integer", description="超时 小时",),
     *   @OA\Property(property="tip", type="number", description="小费",),
     *   @OA\Property(property="ship_id",type="integer",description="收货地址id",),
     *   @OA\Property(property="type", type="integer", description="类型 2取快递3外卖代拿4校园跑腿5其他帮助",),
     *   @OA\Property(property="coupon_ids",type="array",description="优惠券", @OA\Items(example=1)),
     *   @OA\Property(property="pay_platform",type="integer",example="2",description="支付方式 2微信3支付宝",),
     *   @OA\Property(property="pay_type",type="string",example="qr",description="支付类型 (查看获取支付参数接口)",),
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new OrderRunerrands();
        if ($order = $model->saveData($params)) {
            return $this->success(['id'=>$order->id]);
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/runerrands/order/update",
     *   summary="跑腿抢单",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="id",type="integer",example="1",description="id",),
     *         @OA\Property(property="status",type="integer",example="1",description="1正常2取消",),
     *         @OA\Property(property="complete",type="integer",example="1",description="1确认收货",),
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
     *   schema="OrderRunerrandsUpdate",
     *   description="跑腿订单数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/OrderRunerrandsCreate"),
     *   }
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel($this->updateCondition(empty($params['id']) ? [] : ['id'=>$params['id']]));
        if (!empty($params['status'])) {
            $model->status = $params['status'];
        }
        if (!empty($params['status'])) {
            $model->complete = 1;
            $model->complete_at = time();
        }

        return $model->save()? $this->success() : $this->fail('请重试');
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Order the loaded model
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        }

        Tools::breakOff(40001);
    }

    /**
     *
     * @OA\Post(path="/runerrands/order/delivery",
     *   summary="跑腿送货",
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
            if (Order::updateAll(['receiver' => $this->current_user_id, 'receiver_at'=>time()],['id'=>$order_id, 'status'=>0]) === false) {
                Yii::$app->redis->del($key);
                return $this->fail('请重试');
            }
        }
        Yii::$app->redis->del($key);
        return $this->fail('差一点点运气，该单已被抢走～～');
    }
}
