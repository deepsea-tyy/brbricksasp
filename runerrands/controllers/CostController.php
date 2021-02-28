<?php

namespace bricksasp\runerrands\controllers;

use Yii;
use bricksasp\base\Tools;
use yii\data\ActiveDataProvider;
use bricksasp\models\RunerrandsCost;
use bricksasp\models\StudentAuth;
use bricksasp\models\redis\Token;

class CostController extends \bricksasp\base\BackendController
{

	public function loginAction()
	{
		return [
			'view',
		];
	}

    /**
     * @OA\Get(path="/runerrands/cost/index",
     *   summary="跑腿费用设置列表",
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
        $query =  RunerrandsCost::find();

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
     * @OA\Get(path="/runerrands/cost/view",
     *   summary="跑腿费用设置详情",
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
     *       @OA\Schema(ref="#/components/schemas/RunerrandsCostUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $map = empty($params['id']) ? ['owner_id'=>$this->current_owner_id] : ['id'=>$params['id']];
        $model = $this->findModel($map);
        $data = $model->toArray();
        $data['weight_cost'] = $model->weithtCost;
        if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND) {
            $std = StudentAuth::find()->where(['user_id'=>$this->current_user_id])->one();
            $data['setting'] = $std->costSetting??[];
        }
        return $this->success($data);
    }

    /**
     * @OA\Post(path="/runerrands/cost/create",
     *   summary="创建跑腿费用设置",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/RunerrandsCostCreate"
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
     *   schema="RunerrandsCostCreate",
     *   description="跑腿费用设置",
     *   @OA\Property(property="basic_cost", type="number", description="基础费"),
     *   @OA\Property(property="lunch_time_cost", type="number", description="特殊时段费"),
     *   @OA\Property(property="dinner_time_cost", type="number", description="特殊时段费"),
     *   @OA\Property(property="difficulty_cost", type="number", description="难度费5楼以上",),
     *   @OA\Property(property="weather_cist", type="number", description="天气费",),
     *   @OA\Property(property="platform_perc", type="integer", description="平台抽成",),
     *   @OA\Property(property="stationmaster_perc", type="integer", description="站长抽成",),
     *   @OA\Property(property="settlement_type", type="integer", description="结算方式1微信零钱2银行卡",),
     *   @OA\Property(property="settlement_least", type="number", description="最低结算金额",),
     *   @OA\Property(property="settlement_date", type="integer", description="结算日期",),
     *   @OA\Property(property="weight_cost", type="array", @OA\Items(
     *     @OA\Property(property="id", type="integer", description="id",),
     *     @OA\Property(property="title", type="string", description="重量范围",),
     *     @OA\Property(property="price", type="number", description="价格",),
     *   ), description="附加总量",)
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new RunerrandsCost();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/runerrands/cost/update",
     *   summary="修改跑腿费用设置",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/RunerrandsCostUpdate"
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
     *   schema="RunerrandsCostUpdate",
     *   description="跑腿费用设置数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/RunerrandsCostCreate"),
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
     * @OA\Post(path="/runerrands/cost/delete",
     *   summary="删除跑腿费用设置",
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
        return RunerrandsCost::deleteAll(['id'=>$params['ids']??0]) ? $this->success() : $this->fail();
    }

    /**
     * Finds the RunerrandsCost model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return RunerrandsCost the loaded model
     */
    protected function findModel($id)
    {
        if (($model = RunerrandsCost::findOne($id)) !== null) {
            return $model;
        }

        Tools::breakOff(40001);
    }
}
