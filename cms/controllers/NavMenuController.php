<?php

namespace bricksasp\cms\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\cms\models\Nav;
use yii\data\ActiveDataProvider;
use bricksasp\cms\models\NavMenu;
use bricksasp\base\BackendController;

/**
 * NavController implements the CRUD actions for Nav model.
 */
class NavMenuController extends BackendController
{
    /**
     * @OA\Get(path="/cms/nav-menu/index",
     *   summary="导航菜单列表",
     *   tags={"cms模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(name="nav_id",in="query",required=true,@OA\Schema(type="integer"),description="导航id"),
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
     * 
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => NavMenu::find()
            ->andFilterWhere($this->ownerCondition())
            ->filterWhere(['nav_id'=>Yii::$app->request->get('nav_id')]),
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
     * @OA\Get(path="/cms/nav-menu/view",
     *   summary="前台导航菜单详情",
     *   tags={"cms模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(description="id",name="id",in="query",@OA\Schema(type="integer")),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(ref="#/components/schemas/navMenuCreate"),
     *     ),
     *   ),
     * )
     *
     * 
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel($params['id'] ?? 0);
        return $this->success($model);
    }

    /**
     * @OA\Post(path="/cms/nav-menu/create",
     *   summary="创建前台导航菜单",
     *   tags={"cms模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/navMenuCreate"
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
     *   schema="navMenuCreate",
     *   description="导航数据结构",
     *   @OA\Property(property="nav_id",type="integer",description="导航 id",),
     *   @OA\Property(property="parent_id",type="integer",description="父 id",),
     *   @OA\Property(property="status",type="integer",description="状态;1:显示;0:隐藏",),
     *   @OA\Property(property="sort",type="integer",description="排序",),
     *   @OA\Property(property="name",type="string",description="菜单名称",),
     *   @OA\Property(property="target",type="string",description="打开方式",),
     *   @OA\Property(property="href",type="string",description="链接",),
     *   @OA\Property(property="icon",type="string",description="图标",),
     * )
     */
    public function actionCreate()
    {
        $model = new NavMenu();
        $params = $this->queryMapPost();

        if ($model->load($params) && $model->save()) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/cms/nav-menu/update",
     *   summary="修改前台导航菜单",
     *   tags={"cms模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/navMenuUpdate"
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
     *   schema="navMenuUpdate",
     *   description="更新导航数据结构",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/navMenuCreate"),
     *   }
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel($this->updateCondition(['id'=>$params['id'] ?? 0]));

        if ($model->load($params) && $model->save()) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/cms/nav-menu/delete",
     *   summary="删除导航菜单",
     *   tags={"cms模块"},
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
        return NavMenu::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }

    /**
     * Finds the NavMenu model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return NavMenu the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = NavMenu::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
    }
}
