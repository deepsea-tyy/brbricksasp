<?php

namespace bricksasp\user\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\RealNameAuth;
use yii\data\ActiveDataProvider;
use bricksasp\base\BackendController;

class RealNameAuthController extends BackendController
{
    /**
     * @OA\Get(path="/user/real-name-auth/index",
     *   summary="实名认证列表",
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
        $query =  RealNameAuth::find();
        $query->andFilterWhere([
            'status' => $params['status']??null,
        ]);
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
     * @OA\Get(path="/user/real-name-auth/view",
     *   summary="实名认证详情",
     *   tags={"user模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token",),
     *   @OA\Parameter(name="user_id",in="query",@OA\Schema(type="integer"),description="user_id",),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       
     *       @OA\Schema(ref="#/components/schemas/RealNameAuthUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel($this->updateCondition(empty($params['user_id']) ? [] : ['user_id'=>$params['user_id']]));
        $data = $model->toArray();
        
        return $this->success($data);
    }

    /**
     * @OA\Post(path="/user/real-name-auth/create",
     *   summary="创建实名认证",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token",),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/RealNameAuthCreate"
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
     *   schema="RealNameAuthCreate",
     *   description="实名认证",
     *   @OA\Property(property="name", type="string", description="真实姓名"),
     *   @OA\Property(property="gender", type="integer", description="性别"),
     *   @OA\Property(property="id_card_no", type="string", description="身份证号"),
     *   @OA\Property(property="id_card_frontal_photo", type="string", description="身份证正面照",),
     *   @OA\Property(property="id_card_reverse_photo", type="string", description="身份证反面照"),
     *   @OA\Property(property="status", type="integer", description="0未审核1通过2拒绝"),
     *   @OA\Property(property="refuse_reasons", type="string", description="拒绝原因"),
     *   required={"name"}
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new RealNameAuth();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/user/real-name-auth/update",
     *   summary="修改实名认证",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token",),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/RealNameAuthUpdate"
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
     *   schema="RealNameAuthUpdate",
     *   description="实名认证数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="user_id", type="integer", description="user_id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/RealNameAuthCreate"),
     *   }
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel($this->updateCondition(empty($params['user_id']) ? [] : ['user_id'=>$params['user_id']]));

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/user/real-name-auth/delete",
     *   summary="删除实名认证",
     *   tags={"user模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token",),
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

        $transaction = RealNameAuth::getDb()->beginTransaction();
        try {
            RealNameAuth::deleteAll($this->updateCondition(['user_id'=>$params['ids']??0]));
            $transaction->commit();
            return $this->success();
        } catch(\Throwable $e) {
            $transaction->rollBack();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Finds the RealNameAuth model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return RealNameAuth the loaded model
     */
    protected function findModel($id)
    {
        if (($model = RealNameAuth::findOne($id)) !== null) {
            return $model;
        }

        Tools::breakOff(40001);
    }
}
