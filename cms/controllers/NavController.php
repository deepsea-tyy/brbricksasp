<?php

namespace bricksasp\cms\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\cms\models\Nav;
use yii\data\ActiveDataProvider;
use bricksasp\models\redis\Token;
use bricksasp\base\BackendController;

/**
 * NavController implements the CRUD actions for Nav model.
 */
class NavController extends BackendController
{
    public function noLoginAction()
    {
        return [
            'index',
            'detail',
        ];
    }

    /**
     * 免登录访问token存在时获取user_id
     * @return array
     */
    public function checkLoginAction() {
        return [
            'index'
        ];
    }

    /**
     * @OA\Get(path="/cms/nav/index",
     *   summary="导航列表",
     *   tags={"cms模块"},
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
        
        $query = Nav::find();
        $query->andFilterWhere($this->ownerCondition());
        $query->andFilterWhere(['like', 'name', $params['name']??null]);
        if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND && empty($params['user_data'])) {
            $query->andFilterWhere(['status' => 1]);
        }
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
     * @OA\Get(path="/cms/nav/view",
     *   summary="导航详情",
     *   tags={"cms模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="id"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(ref="#/components/schemas/navUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel($params['id'] ?? 0);
        return $this->success($model);
    }

    /**
     * @OA\Get(path="/cms/nav/detail",
     *   summary="前台导航详情",
     *   tags={"cms模块"},
     *   
     *   @OA\Parameter(name="code",in="query",@OA\Schema(type="string",description="导航调用代码",default="home_nav")),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/AdvertPositionDetail"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionDetail()
    {
        $model = Nav::find()->with(['menu'])->where(['code'=>Yii::$app->request->get('code','home_nav')])->one();
        if (!$model) {
            Tools::breakOff(40001);
        }
        $data = $model->toArray();
        $data['menu'] = $model->menu;
        return $this->success($data);
    }

    /**
     * @OA\Post(path="/cms/nav/create",
     *   summary="创建前台导航位置",
     *   tags={"cms模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/navCreate"
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
     *   schema="navCreate",
     *   description="导航数据结构",
     *   @OA\Property(property="name", type="string", description="导航名称"),
     *   @OA\Property(property="code", type="string", description="调用代码"),
     *   @OA\Property(property="remark", type="string", description="备注"),
     *   @OA\Property(property="status", type="integer", description="1启用0关闭"),
     *   required={"name"}
     * )
     */
    public function actionCreate()
    {
        $model = new Nav();
        $params = $this->queryMapPost();
        
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/cms/nav/update",
     *   summary="修改前台导航",
     *   tags={"cms模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/navUpdate"
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
     *   schema="navUpdate",
     *   description="更新导航数据结构",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/navCreate"),
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
     * @OA\Post(path="/cms/nav/delete",
     *   summary="删除导航",
     *   tags={"cms模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
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
     */
    public function actionDelete()
    {
        $params = $this->queryMapPost();
        if (Nav::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }
        return Nav::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }

    /**
     * Finds the Nav model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Nav the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Nav::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
    }
}
