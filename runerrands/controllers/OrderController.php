<?php

namespace bricksasp\runerrands\controllers;

use Yii;
use bricksasp\base\Tools;
use yii\data\ActiveDataProvider;
use bricksasp\models\OrderRunerrands;

class OrderController extends \bricksasp\base\BackendController
{

	public function noLoginAction()
	{
		return [
			'index',
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
        $query =  OrderRunerrands::find();
        $query->andFilterWhere(['like', 'name', $params['name']??null]);

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
        
        return $this->success($model);
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
     *   @OA\Property(property="order_id", type="number", description="order_id"),
     *   @OA\Property(property="content", type="number", description="办事内容"),
     *   @OA\Property(property="start_place", type="number", description="起始地"),
     *   @OA\Property(property="end_place", type="number", description="目的地",),
     *   @OA\Property(property="time", type="number", description="办事时间",),
     *   @OA\Property(property="gender", type="integer", description="0女1男",),
     *   @OA\Property(property="overtime", type="integer", description="超时 小时",),
     *   @OA\Property(property="tip", type="number", description="小费",),
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
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/runerrands/order/update",
     *   summary="修改跑腿订单",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/OrderRunerrandsUpdate"
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

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/runerrands/order/delete",
     *   summary="删除跑腿订单",
     *   tags={"跑腿模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
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
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     *
     * 
     */
    public function actionDelete()
    {
        $params = $this->queryMapPost();
        return OrderRunerrands::deleteAll(['id'=>$params['ids']??0]) ? $this->success() : $this->fail();
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Order the loaded model
     */
    protected function findModel($id)
    {
        if (($model = OrderRunerrands::findOne($id)) !== null) {
            return $model;
        }

        Tools::breakOff(40001);
    }
}
