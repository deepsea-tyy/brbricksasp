<?php

namespace bricksasp\user\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\ShoppingCart;
use yii\data\ActiveDataProvider;
use bricksasp\base\BackendController;

/**
 * ShoppingCartController implements the CRUD actions for ShoppingCart model.
 */
class ShoppingCartController extends BackendController
{
    public function noLoginAction()
    {
        return [];
    }

    /**
     * @OA\Get(path="/user/shopping-cart/index",
     *   summary="购物车列表",
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
        $query =  ShoppingCart::find();
        $query->andFilterWhere([
            'status' => $params['status']??null,
        ]);
        $query->andFilterWhere(['user_id'=>$this->current_user_id]);

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
     * @OA\Get(path="/user/shopping-cart/view",
     *   summary="购物车详情",
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
     *       @OA\Schema(ref="#/components/schemas/ShoppingCartView"),
     *     ),
     *   ),
     * )
     *
     * @OA\Schema(
     *   schema="ShoppingCartView",
     *   description="购物车数据详情",
     *   allOf={
     *     @OA\Schema(ref="#/components/schemas/ShoppingCartUpdate"),
     *   }
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel(['user_id'=>$this->current_user_id,'id'=>$params['id']??0]);
        return $this->success($model);
    }

    /**
     * @OA\Post(path="/user/shopping-cart/create",
     *   summary="创建购物车",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/ShoppingCartCreate"
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
     *   schema="ShoppingCartCreate",
     *   description="购物车",
     *   @OA\Property(property="product_id", type="integer", description="单品id"),
     *   @OA\Property(property="num", type="integer", description="数量"),
     *   required={"name"}
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $map = ['user_id'=>$this->current_user_id,'product_id'=>$params['product_id']??0];
        $model = ShoppingCart::findOne($map);
        if ($model) {
            return ShoppingCart::updateAllCounters(['num'=>$params['num']??1], $map) ? $this->success():$this->fail();
        }
        $model = new ShoppingCart();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/user/shopping-cart/update",
     *   summary="修改购物车",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/ShoppingCartUpdate"
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
     *   schema="ShoppingCartUpdate",
     *   description="购物车数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/ShoppingCartCreate"),
     *   }
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel(['user_id'=>$this->current_user_id,'id'=>$params['id']??0]);

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/user/shopping-cart/delete",
     *   summary="删除购物车",
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
        $transaction = ShoppingCart::getDb()->beginTransaction();
        try {
            ShoppingCart::deleteAll(['user_id'=>$this->current_user_id, 'id'=>$params['ids']??0]);
            $transaction->commit();
            return $this->success();
        } catch(\Throwable $e) {
            $transaction->rollBack();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Finds the ShoppingCart model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ShoppingCart the loaded model
     */
    protected function findModel($id)
    {
        if (($model = ShoppingCart::findOne($id)) !== null) {
            return $model;
        }

        Tools::breakOff(40001);
    }
}
