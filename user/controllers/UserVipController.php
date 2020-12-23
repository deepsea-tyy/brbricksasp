<?php

namespace bricksasp\user\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\UserVip;
use yii\data\ActiveDataProvider;
use bricksasp\base\BackendController;

/**
 * UserVipController implements the CRUD actions for UserVip model.
 */
class UserVipController extends BackendController
{
    public function noLoginAction()
    {
        return [];
    }

    /**
     * @OA\Get(path="/user/user-vip/index",
     *   summary="会员等级列表",
     *   tags={"user模块"},
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
    public function actionIndex()
    {
        $params = Yii::$app->request->get();
        $query =  UserVip::find();
        $query->andFilterWhere([
            'status' => $params['status']??null,
        ]);
        $query->andFilterWhere($this->ownerCondition());
        $query->andFilterWhere(['is_delete'=> empty($params['is_delete']) ? 0 : 1]);

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
     * @OA\Get(path="/user/user-vip/view",
     *   summary="会员等级详情",
     *   tags={"user模块"},
     *   
     *   @OA\Parameter(description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\Parameter(description="id",name="id",in="query",@OA\Schema(type="integer")),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       
     *       @OA\Schema(ref="#/components/schemas/UserVipUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel($this->updateCondition(['id'=>$params['id'] ?? 0]));
        $data = $model->toArray();
        
        if ($model->company) {
            $data['company'] = $model->company->toArray();
            $data['company']['gps'] = json_decode($model->company->gps,true);
        }
        $data['companyQualifications'] = $model->companyQualifications;
        return $this->success($data);
    }

    /**
     * @OA\Post(path="/user/user-vip/create",
     *   summary="创建会员等级",
     *   tags={"user模块"},
     *   @OA\Parameter(description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/UserVipCreate"
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
     *   schema="UserVipCreate",
     *   description="会员等级",
     *   @OA\Property(property="level", type="integer", description="等级"),
     *   @OA\Property(property="up_cdt", type="integer", description="升级条件1订单金额/数量2指定商品"),
     *   @OA\Property(property="up_cdt_val", type="string", description="条件值"),
     *   @OA\Property(property="discount", type="string", description="折扣",),
     *   @OA\Property(property="duration", type="integer", description="会员时间期限 月",),
     *   @OA\Property(property="status", type="integer", description="1启用",),
     *   required={"name"}
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new UserVip();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/user/user-vip/update",
     *   summary="修改会员等级",
     *   tags={"user模块"},
     *   @OA\Parameter(description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/UserVipUpdate"
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
     *   schema="UserVipUpdate",
     *   description="会员等级数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/UserVipCreate"),
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
     * @OA\Post(path="/user/user-vip/delete",
     *   summary="删除会员等级",
     *   tags={"user模块"},
     *   
     *   @OA\Parameter(description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
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
        if (UserVip::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }

        return UserVip::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }

    /**
     * Finds the UserVip model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserVip the loaded model
     */
    protected function findModel($id)
    {
        if (($model = UserVip::findOne($id)) !== null) {
            return $model;
        }

        Tools::breakOff(40001);
    }
}
