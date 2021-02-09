<?php

namespace bricksasp\user\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\ShipAddress;
use yii\data\ActiveDataProvider;
use bricksasp\base\BackendController;

/**
 * ShipAddressController implements the CRUD actions for ShipAddress model.
 */
class ShipAddressController extends BackendController
{
    public function noLoginAction()
    {
        return [];
    }

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
     * @OA\Get(path="/user/ship-address/index",
     *   summary="收货地址列表",
     *   tags={"user模块"},
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
        $query =  ShipAddress::find();
        $query->andFilterWhere($this->ownerCondition());

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
     * @OA\Get(path="/user/ship-address/view",
     *   summary="收货地址详情",
     *   tags={"user模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="id"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       
     *       @OA\Schema(ref="#/components/schemas/ShipAddressUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel($this->updateCondition(['id'=>$params['id'] ?? 0]));
        
        return $this->success($model);
    }

    /**
     * @OA\Post(path="/user/ship-address/create",
     *   summary="创建收货地址",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/ShipAddressCreate"
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
     *   schema="ShipAddressCreate",
     *   description="收货地址",
     *   @OA\Property(property="name", type="string", description="收货人姓名"),
     *   @OA\Property(property="area_id", type="integer", description="收货地区ID"),
     *   @OA\Property(property="address", type="string", description="收货详细地址"),
     *   @OA\Property(property="phone", type="string", description="收货电话",),
     *   @OA\Property(property="is_default", type="integer", description="是否默认 1是"),
     *   @OA\Property(property="school", type="string", description="学校名称"),
     *   @OA\Property(property="school_area", type="string", description="学校校区"),
     *   @OA\Property(property="building_no", type="string", description="楼号"),
     *   @OA\Property(property="floor", type="integer", description="楼层"),
     *   @OA\Property(property="house_number", type="integer", description="门牌号"),
     *   required={"name"}
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new ShipAddress();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/user/ship-address/update",
     *   summary="修改收货地址",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/ShipAddressUpdate"
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
     *   schema="ShipAddressUpdate",
     *   description="收货地址数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/ShipAddressCreate"),
     *   }
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel($this->updateCondition(['id'=>$params['id'] ?? 0]));

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/user/ship-address/delete",
     *   summary="删除收货地址",
     *   tags={"user模块"},
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

        $transaction = ShipAddress::getDb()->beginTransaction();
        try {
            ShipAddress::deleteAll($this->updateCondition(['id'=>$params['ids']??0]));
            $transaction->commit();
            return $this->success();
        } catch(\Throwable $e) {
            $transaction->rollBack();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Finds the ShipAddress model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ShipAddress the loaded model
     */
    protected function findModel($id)
    {
        if (($model = ShipAddress::findOne($id)) !== null) {
            return $model;
        }

        Tools::breakOff(40001);
    }
}
