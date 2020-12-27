<?php

namespace bricksasp\runerrands\controllers;

use Yii;
use bricksasp\models\School;
use yii\data\ActiveDataProvider;

class SchoolController extends \bricksasp\base\BaseController
{

	public function noLoginAction()
	{
		return [
			'index',
		];
	}

    /**
     * @OA\Get(path="/runerrands/school/index",
     *   summary="学校列表",
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
        $query =  School::find();
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
     * @OA\Get(path="/runerrands/school/view",
     *   summary="学校详情",
     *   tags={"跑腿模块"},
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
     *       @OA\Schema(ref="#/components/schemas/SchoolUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel(['id'=>$params['id'] ?? 0]);
        
        return $this->success($model);
    }

    /**
     * @OA\Post(path="/runerrands/school/create",
     *   summary="创建学校",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/SchoolCreate"
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
     *   schema="SchoolCreate",
     *   description="学校",
     *   @OA\Property(property="name", type="string", description="学校名称"),
     *   @OA\Property(property="code", type="integer", description="学校标识码"),
     *   @OA\Property(property="parent_id", type="integer", description="0主校区 其他表示分校区"),
     *   @OA\Property(property="level", type="string", description="1本科2专科",),
     *   @OA\Property(property="city", type="string", description="学校所在城市",),
     *   @OA\Property(property="address", type="string", description="学校详细地址",),
     *   @OA\Property(property="logo", type="string", description="logo",),
     *   @OA\Property(property="mark", type="string", description="备注",),
     *   required={"name"}
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new School();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/runerrands/school/update",
     *   summary="修改学校",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/SchoolUpdate"
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
     *   schema="SchoolUpdate",
     *   description="学校数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/SchoolCreate"),
     *   }
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel(['id'=>$params['id'] ?? 0]);

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/runerrands/school/delete",
     *   summary="删除学校",
     *   tags={"跑腿模块"},
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
        return School::deleteAll(['id'=>$params['ids']??0]) ? $this->success() : $this->fail();
    }

    /**
     * Finds the School model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return School the loaded model
     */
    protected function findModel($id)
    {
        if (($model = School::findOne($id)) !== null) {
            return $model;
        }

        Tools::breakOff(40001);
    }
}
