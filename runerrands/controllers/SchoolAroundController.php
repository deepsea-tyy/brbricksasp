<?php

namespace bricksasp\runerrands\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\SchoolAround;
use yii\data\ActiveDataProvider;

class SchoolAroundController extends \bricksasp\base\BackendController
{
	public function noLoginAction()
	{
		return [
			'index',
            'view',
		];
	}

    public function loginAction()
    {
        return [
            'update',
        ];
    }

    /**
     * @OA\Get(path="/runerrands/school-around/index",
     *   summary="学校周边列表",
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
        $query =  SchoolAround::find();
        $query->andFilterWhere(['school_id'=>$params['school_id']??Tools::breakOff('未找学校相应到数据')]);
        $query->andFilterWhere(['like', 'name', $params['name']??null]);

        if (!empty($params['school'])) {
            $query->with(['school']);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $list = [];
        if (!empty($params['school'])) {
            foreach ($dataProvider->models as $item) {
                $row = $item->toArray();
                $row['school'] = $item->school;
                $list[] = $row;
            }
        }

        return $this->success([
          'list' => $list?$list:$dataProvider->models,
          'pageCount' => $dataProvider->pagination->pageCount,
          'totalCount' => $dataProvider->pagination->totalCount,
          'page' => $dataProvider->pagination->page + 1,
          'pageSize' => $dataProvider->pagination->limit,
        ]);
    }

    /**
     * @OA\Get(path="/runerrands/school-around/view",
     *   summary="学校周边详情",
     *   tags={"跑腿模块"},
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
     *       @OA\Schema(ref="#/components/schemas/SchoolAroundUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel(['id'=>$params['id'] ?? 0]);
        if ($model->parent_id) {
            $SchoolAround = $this->findModel(['id'=>$model->parent_id]);
        }
        return $this->success(['SchoolAround'=>empty($SchoolAround)?$model:$SchoolAround, 'area'=>empty($SchoolAround)?[]:$model]);
    }

    /**
     * @OA\Post(path="/runerrands/school-around/create",
     *   summary="创建学校周边",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/SchoolAroundCreate"
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
     *   schema="SchoolAroundCreate",
     *   description="学校周边",
     *   @OA\Property(property="name", type="string", description="地点名称"),
     *   @OA\Property(property="logo", type="string", description="logo"),
     *   @OA\Property(property="address", type="string", description="详细地址"),
     *   @OA\Property(property="area_id", type="integer", description="1本科2专科",),
     *   @OA\Property(property="type", type="integer", description="1取快递2外卖代拿3跑腿",),
     *   @OA\Property(property="lat", type="string", description="",),
     *   @OA\Property(property="lon", type="string", description="",),
     *   required={"name"}
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new SchoolAround();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/runerrands/school-around/update",
     *   summary="修改学校周边",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/SchoolAroundUpdate"
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
     *   schema="SchoolAroundUpdate",
     *   description="学校周边数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/SchoolAroundCreate"),
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
     * @OA\Post(path="/runerrands/school-around/delete",
     *   summary="删除学校周边",
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
        return SchoolAround::deleteAll(['id'=>$params['ids']??0]) ? $this->success() : $this->fail();
    }

    /**
     * Finds the SchoolAround model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SchoolAround the loaded model
     */
    protected function findModel($id)
    {
        if (($model = SchoolAround::findOne($id)) !== null) {
            return $model;
        }

        Tools::breakOff(40001);
    }
}
