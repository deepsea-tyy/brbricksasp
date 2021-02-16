<?php

namespace bricksasp\runerrands\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\RunerrandsRider;
use yii\data\ActiveDataProvider;
use bricksasp\rbac\models\User;

class RiderController extends \bricksasp\base\BackendController
{
	public function noLoginAction()
	{
		return [
			'index',
            'login',
            'repassword',
		];
	}

    public function loginAction()
    {
        return [
            'view',
            'create',
            'update',
            'rank',
        ];
    }

    /**
     * @OA\Get(path="/runerrands/rider/index",
     *   summary="骑手列表",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
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
        $query =  RunerrandsRider::find();
        $query->andFilterWhere($this->ownerCondition());
        $query->andFilterWhere(['school_id'=>$params['school_id']??Tools::breakOff('未找学校相应到数据')]);
        $query->andFilterWhere(['like', 'name', $params['name']??null]);
        $query->andFilterWhere(['status'=>$params['status']??1]);

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
     * @OA\Get(path="/runerrands/rider/view",
     *   summary="骑手详情",
     *   tags={"跑腿模块"},
     *   
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="id"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       
     *       @OA\Schema(ref="#/components/schemas/RunerrandsRiderUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel(['user_id'=>$params['id'] ?? $this->current_user_id]);
        return $this->success($model);
    }

    /**
     * @OA\Post(path="/runerrands/rider/create",
     *   summary="创建骑手",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/RunerrandsRiderCreate"
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
     *   schema="RunerrandsRiderCreate",
     *   description="骑手",
     *   @OA\Property(property="school_id", type="integer", description=""),
     *   @OA\Property(property="school_area_id", type="integer", description=""),
     *   @OA\Property(property="name", type="string", description="姓名"),
     *   @OA\Property(property="phone", type="integer", description="",),
     *   @OA\Property(property="has_car", type="integer", description="1有车",),
     *   @OA\Property(property="status", type="integer", description="",),
     *   @OA\Property(property="refuse_reasons", type="string", description="",),
     *   required={"name"}
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new RunerrandsRider();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/runerrands/rider/update",
     *   summary="修改骑手",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/RunerrandsRiderUpdate"
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
     *   schema="RunerrandsRiderUpdate",
     *   description="骑手数据",
     *   allOf={
     *     @OA\Schema(ref="#/components/schemas/RunerrandsRiderCreate"),
     *   }
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel(['user_id'=>$params['id'] ?? $this->current_user_id]);

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/runerrands/rider/delete",
     *   summary="删除骑手",
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
        return RunerrandsRider::deleteAll(['id'=>$params['ids']??0]) ? $this->success() : $this->fail();
    }

    /**
     * Finds the RunerrandsRider model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return RunerrandsRider the loaded model
     */
    protected function findModel($id)
    {
        if (($model = RunerrandsRider::findOne($id)) !== null) {
            return $model;
        }

        Tools::breakOff(40001);
    }

    /**
     * @OA\Post(path="/runerrands/rider/login",
     *   summary="骑手登录",
     *   tags={"跑腿模块"},
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="phone", type="integer", description="phone"),
     *         @OA\Property(property="password", type="string", description="password"),
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
     */
    public function actionLogin()
    {
        $phone = Yii::$app->request->post('phone');
        $password = Yii::$app->request->post('password');
        $model = RunerrandsRider::findOne(['phone'=>$phone, 'password'=>md5($password)]);
        if (!$model) {
            return $this->fail('账号或密码错误');
        }
        $data = User::generateApiToken($model->user_id, 1);
        $data['school_id'] = $model->school_id;
        return $this->success($data,'登录成功');
    }

    /**
     * @OA\Post(path="/runerrands/rider/repassword",
     *   summary="骑手登录",
     *   tags={"跑腿模块"},
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="phone", type="integer", description="phone"),
     *         @OA\Property(property="password", type="string", description="password"),
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
     */
    public function actionRepassword()
    {
        $phone = Yii::$app->request->post('phone');
        $password = Yii::$app->request->post('password');
        $model = $this->findModel(['phone'=>$phone, 'password'=>md5($password)]);
    }

    /**
     * @OA\Get(path="/runerrands/rider/rank",
     *   summary="骑手排行",
     *   tags={"跑腿模块"},
     *   
     *   @OA\Parameter(name="school_id",in="query",@OA\Schema(type="integer"),description="学校id"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       
     *       @OA\Schema(ref="#/components/schemas/RunerrandsRiderUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionRank()
    {
        $school_id = Yii::$app->request->get('school_id');
        $models = RunerrandsRider::find()->with(['userInfo'])->where(['school_id'=>$school_id??Tools::breakOff(50001)])->orderBy('total_amount desc')->all();
        $list = [];
        foreach ($models as $item) {
            $row = $item->toArray();
            $row['file'] = $item->userInfo->file??[];
            $list[] = $row;
        }
        return $this->success($list);
    }
}
