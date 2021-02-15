<?php

namespace bricksasp\user\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\UserFund;
use bricksasp\models\UserFundLog;
use bricksasp\models\DrawMoney;
use yii\data\ActiveDataProvider;
use bricksasp\base\BackendController;

/**
 * UserFundController implements the CRUD actions for UserFund model.
 */
class UserFundController extends BackendController
{
    public function loginAction()
    {
        return [
            'log',
            'draw-money',
            'draw-money-list'
        ];
    }

    /**
     * @OA\Get(path="/user/user-fund/index",
     *   summary="会员资产列表",
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
        $query =  UserFund::find();
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
     * @OA\Get(path="/user/user-fund/view",
     *   summary="会员资产详情",
     *   tags={"user模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token",),
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="id",),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       
     *       @OA\Schema(ref="#/components/schemas/UserFundUpdate"),
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
     * @OA\Post(path="/user/user-fund/create",
     *   summary="创建会员资产",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token",),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/UserFundCreate"
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
     *   schema="UserFundCreate",
     *   description="会员资产",
     *   @OA\Property(property="point", type="integer", description="数量"),
     *   @OA\Property(property="status", type="integer", description="1入账2入账"),
     *   @OA\Property(property="type", type="integer", description="1money2积分3信用分"),
     *   @OA\Property(property="object_id", type="integer", description="来源id",),
     *   @OA\Property(property="object_type", type="integer", description="1跑腿",),
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new UserFund();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/user/user-fund/update",
     *   summary="修改会员资产",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token",),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/UserFundUpdate"
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
     *   schema="UserFundUpdate",
     *   description="会员资产数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/UserFundCreate"),
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
     * @OA\Post(path="/user/user-fund/delete",
     *   summary="删除会员资产",
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
        if (UserFund::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }

        return UserFund::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }

    /**
     * Finds the UserFund model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserFund the loaded model
     */
    protected function findModel($id)
    {
        if (($model = UserFund::findOne($id)) !== null) {
            return $model;
        }

        Tools::breakOff(40001);
    }

    /**
     * @OA\Get(path="/user/user-fund/log",
     *   summary="会员资产流水列表",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
     *   @OA\Parameter(name="status",in="query",@OA\Schema(type="integer"),description="1入账2出账"),
     *   @OA\Parameter(name="object_type",in="query",@OA\Schema(type="integer"),description="1订单2提现"),
     *   @OA\Parameter(name="scene",in="query",@OA\Schema(type="integer"),description="1跑腿",example=1),
     *   @OA\Parameter(name="dateTime",in="query",@OA\Schema(type="integer"),description="默认当月"),
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
    public function actionLog()
    {
        $params = Yii::$app->request->get();
        $query =  UserFundLog::find();
        $query->andFilterWhere($this->ownerCondition());
        $query->andFilterWhere(['object_type'=>$params['object_type']??null]);
        $query->andFilterWhere(['status'=>$params['status']??null]);
        $query->andFilterWhere(['scene'=>$params['scene']??null]);
        
        $params['dateTime'] = empty($params['dateTime'])? time() : $params['dateTime'];
        $start = strtotime(date('Y-m',$params['dateTime']));
        $end = strtotime(date('Y-m',$params['dateTime']) . ' +1 month');
        $query->andFilterWhere(['between', 'created_at', $start, $end]);
        
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
     * @OA\Post(path="/user/user-fund/draw-money",
     *   summary="创建会员资产",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token",),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/DrawMoneyCreate"
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
     *   schema="DrawMoneyCreate",
     *   description="提现",
     *   @OA\Property(property="money", type="number", description="提现金额"),
     *   @OA\Property(property="status", type="number", description="1提现成功2拒绝"),
     *   @OA\Property(property="platform", type="integer", description="1微信2支付宝"),
     *   @OA\Property(property="draw_type", type="integer", description="1零钱"),
     *   @OA\Property(property="scene", type="integer", description="1跑腿",example=1),
     * )
     */
    public function actionDrawMoney()
    {
        $params = $this->queryMapPost();
        $model = new DrawMoney();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Get(path="/user/user-fund/draw-money-list",
     *   summary="会员提现列表",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
     *   @OA\Parameter(name="status",in="query",@OA\Schema(type="integer"),description="1提现成功2撤销"),
     *   @OA\Parameter(name="scene",in="query",@OA\Schema(type="integer"),description="1跑腿",example=1),
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
    public function actionDrawMoneyList()
    {
        $params = Yii::$app->request->get();
        $query =  DrawMoney::find()->asArray();
        $query->andFilterWhere($this->ownerCondition());
        $query->andFilterWhere([
            'status'=>$params['status']??null,
            'scene'=>$params['scene']??1,
        ]);
        
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
}
