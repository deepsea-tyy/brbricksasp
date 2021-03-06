<?php

namespace bricksasp\runerrands\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\Order;
use yii\data\ActiveDataProvider;
use bricksasp\models\RunerrandsCost;
use bricksasp\models\OrderRunerrands;
use bricksasp\models\UserFundLog;
use bricksasp\models\UserFund;
use bricksasp\models\OrderSettle;
use bricksasp\models\RunerrandsRider;
use bricksasp\models\redis\Token;

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
            'delivery',
            'complete',
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
     *   @OA\Parameter(name="complete",in="query",@OA\Schema(type="integer"),description="1确认取货2确认送货3确认收货"),
     *   @OA\Parameter(name="pay_status",in="query",@OA\Schema(type="integer"),description="支付状态"),
     *   @OA\Parameter(name="receiver",in="query",@OA\Schema(type="integer"),description="1待抢"),
     *   @OA\Parameter(name="delivery",in="query",@OA\Schema(type="integer"),description="接单列表"),
     *   @OA\Parameter(name="school_id",in="query",@OA\Schema(type="integer"),description="学校id"),
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
        $query = Order::find();
        $with = ['runerrands'];
        $query->orderBy('created_at desc');
        $with[] = 'shipAddress';
        if ($this->current_login_type == Token::TOKEN_TYPE_BACKEND) {
            $query->andFilterWhere($this->updateCondition(['type'=>[2,3,4,5]]));
            $with[] = 'school';
            $with[] = 'realName';
        }else{
            if (empty($params['delivery'])) {
                $query->andFilterWhere(['pay_status'=>$params['pay_status']??null]);
                $query->andFilterWhere(['complete'=>empty($params['complete'])?null:explode(',',$params['complete'])]);
                if (isset($params['complete'])||isset($params['pay_status'])) {
                    $query->andWhere(isset($params['complete'])?['not', ['receiver' => null]]:['receiver' => null]);
                }
            }else {//代接单
                $with[] = 'runerrandsWeight';
                $with[] = 'runerrandsStartPlace';
                $map = ['pay_status'=>Order::PAY_ALL, 'receiver'=>null, 'status'=>Order::STATUS_NORMAL];
                if ($params['delivery'] == 1) {//待抢
                    $ods = OrderRunerrands::find()->select(['order_id'])->where(['school_id'=>$params['school_id']??Tools::breakOff(50001)])->asArray()->all();
                    $map['id'] = array_column($ods,'order_id');
                }else{
                    $map['receiver'] = $this->current_user_id;
                    $map['complete'] = empty($params['complete'])?null : explode(',',$params['complete']);
                }

                $query->andWhere($map);
                $query->andFilterWhere(['type'=>[2,3,4,5]]);
            }
        }
        $query->with($with);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $list=[];
        foreach ($dataProvider->models as $item) {
            $row = $item->toArray();
            foreach ($with as $field) {
                $row[$field] = $item->$field;
            }
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
        $model = $this->findModel($params['id']??null);
        
        $data = $model->toArray();
        $data['runerrands'] = $model->runerrands;
        $data['shipAddress'] = $model->shipAddress;
        $data['rider'] = $model->rider;
        $data['riderRealNmae'] = $model->rider?$model->rider->realName:null;
        $data['runerrandsWeight'] = $model->runerrandsWeight;
        $data['runerrandsStartPlace'] = $model->runerrandsStartPlace;
        $data['student'] = $model->student;
        $data['realName'] = $model->realName;
        $data['user'] = $model->user;
        $data['school'] =$model->student?$model->student->school:null;
        $data['schoolArea'] =$model->student?$model->student->schoolArea:null;
        $cost = RunerrandsCost::findOne(['owner_id'=>$this->current_owner_id]);

        $platform_perc_price = $model->pay_price * ($cost->platform_perc + $cost->stationmaster_perc)/100;
        $data['platform_price'] = $model->pay_price * $cost->platform_perc;
        $data['stationmaster_price'] = $model->pay_price * $cost->stationmaster_perc;
        $data['platform_perc_price'] = $platform_perc_price;
        $data['getMoney'] = number_format($model->pay_price - $platform_perc_price, 2, '.', '');

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
     *         @OA\Property(property="complete",type="integer",example="1",description="1确认取货2确认收货",),
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
        if (!empty($params['complete'])) {
            $model->complete = 3;
            $model->complete_at = time();
        }

        $transaction = $model::getDb()->beginTransaction();
        try {
            if ($model->save()) {
                $settle = OrderSettle::find()->where(['order_id'=>$model->id])->one();
                if ($model->complete == 3 && !$settle) {
                    $fund = UserFund::find()->where(['user_id'=>$model->receiver])->one();
                    $cost = RunerrandsCost::find()->where(['owner_id'=>$model->owner_id])->one();

                    $rate = $cost->platform_perc+$cost->stationmaster_perc;
                    $perc = $model->pay_price * $rate /100;
                    $money = $model->pay_price - $perc;
                    $settle = new OrderSettle();
                    $settle->load([
                        'owner_id'=>$model->owner_id,
                        'user_id'=>$model->receiver,
                        'order_id'=>$model->id,
                        'status'=>1,
                        'money'=>$money,
                    ]);
                    

                    $log = new UserFundLog();
                    $log->load([
                        'owner_id'=>$model->owner_id,
                        'user_id'=>$model->receiver,
                        'status'=>1,
                        'type'=>1,
                        'point'=>$money,
                        'object_id'=>$model->id,
                        'object_type'=>1,
                        'perc'=>$perc,
                        'amount' => $fund->amount + $money,
                    ]);

                    if (!$settle->save() 
                        || !$log->save() 
                        || !UserFund::updateAllCounters(['amount'=>$money],['user_id'=>$model->receiver])
                        || !RunerrandsRider::updateAllCounters(['total_amount'=>$money,'total_order'=>1],['user_id'=>$model->receiver])
                    ) {
                        $transaction->rollBack();
                        Tools::breakOff('请重试');
                    }
                }
                $transaction->commit();
                return $this->success();
            }
        } catch(\Throwable $e) {
            $transaction->rollBack();
        }
        return $this->fail('请重试');
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
     * @OA\Post(path="/runerrands/order/delivery",
     *   summary="跑腿抢单送货",
     *   tags={"跑腿模块"},
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
            if (Order::updateAll(['receiver' => $this->current_user_id, 'receiver_at'=>time()],['id'=>$order_id, 'receiver'=>null]) === false) {
                Yii::$app->redis->del($key);
                return $this->fail('请重试');
            }
            Yii::$app->redis->del($key);
            return $this->success();
        }
        Yii::$app->redis->del($key);
        return $this->fail('差一点点运气，该单已被抢走～～');
    }

    /**
     * @OA\Post(path="/runerrands/order/complete",
     *   summary="跑腿送货状态",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(name="X-Token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="order_id",type="integer",description="订单id"),
     *         @OA\Property(property="complete",type="integer",description="1确认取货2确认送货"),
     *         @OA\Property(property="transit",type="integer",description="1转单"),
     *         @OA\Property(property="transit_user",type="integer",description="定向接单人"),
     *         @OA\Property(property="cancel",type="integer",description="1取消")
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
    public function actionComplete() {
        $params = $this->queryMapPost();
        if (!empty($params['transit'])) {
            return Order::updateAll(['transit' => $params['current_user_id']],['id'=>$params['order_id']??Tools::breakOff('订单号有误')]) === false ? $this->fail('请重试'):$this->success();
        }
        if (!empty($params['transit_user'])) {
            return Order::updateAll(['receiver' => $params['transit_user']],['id'=>$params['order_id']??Tools::breakOff('订单号有误')]) === false ? $this->fail('请重试'):$this->success();
        }
        if (!empty($params['cancel'])) {
            return Order::updateAll(['receiver' => null],['id'=>$params['order_id']??Tools::breakOff('订单号有误')]) === false ? $this->fail('请重试'):$this->success();
        }
        return Order::updateAll(['complete' => $params['complete']],['id'=>$params['order_id']??Tools::breakOff('订单号有误')]) === false ? $this->fail('请重试'):$this->success();
    }
}
