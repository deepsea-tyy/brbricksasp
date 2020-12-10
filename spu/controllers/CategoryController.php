<?php

namespace bricksasp\spu\controllers;

use Yii;
use bricksasp\base\Tools;
use yii\data\ActiveDataProvider;
use bricksasp\base\BackendController;
use bricksasp\spu\models\GoodsCategory;
use bricksasp\models\redis\Token;

/**
 * CategoryController implements the CRUD actions for GoodsCategory model.
 */
class CategoryController extends BackendController
{
    public function checkLoginAction()
    {
        return [
            'index',
        ];
    }

    public function noLoginAction()
    {
        return [
            'index',
            'child',
            'tree',
        ];
    }

    /**
     * @OA\Get(path="/spu/category/index",
     *   summary="分类列表",
     *   tags={"spu模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
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
        $query = GoodsCategory::find();
        $query->andFilterWhere([
            'name' => $params['name']??null,
        ]);
        
        if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND && empty($params['user_data'])) {
            $query->andFilterWhere(['status' => 1]);
        }
        $query->andFilterWhere($this->ownerCondition());
        $query->andFilterWhere(['like', 'name', $params['name']??null]);
        $query->andFilterWhere(['is_delete'=> empty($params['is_delete']) ? 0 : 1]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query->with(['file']),
        ]);
        
        $list = [];
        foreach ($dataProvider->models as $item) {
            $row = $item->toArray();
            $row['file'] = $item->file ?? [];
            $list[] = $row;
        }
        return $this->success([
          'list' => $list,
          'pageCount' => $dataProvider->pagination->pageCount,
          'totalCount' => $dataProvider->pagination->totalCount,
          'page' => $dataProvider->pagination->page + 1,
          'pageSize' => $dataProvider->pagination->limit,
        ]);
    }

    /**
     * @OA\Get(path="/spu/category/view",
     *   summary="分类详情",
     *   tags={"spu模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="要查看分类的id"),
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
     */
    public function actionView()
    {
        $model = $this->findModel(Yii::$app->request->get('id'));
        $data = $model->toArray();
        $data['file'] = $model->file ?? [];
        return $this->success($data);
    }

    /**
     * @OA\post(path="/spu/category/create",
     *   summary="添加分类",
     *   tags={"spu模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *
     *  @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/goodsCatCreate"
     *       ),
     *     ),
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
     *   schema="goodsCatCreate",
     *   description="分类",
     *   @OA\Property(property="name",type="string",description="分类名称"),
     *   @OA\Property(property="parent_id",type="integer",description="分类的父id"),
     *   @OA\Property(property="sort", type="integer", description="排序"),
     *   @OA\Property(property="status", type="string", description="1显示2不显示"),
     *   required={"name"}
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new GoodsCategory();

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\post(path="/spu/category/update",
     *   summary="修改分类",
     *   tags={"spu模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *
     *  @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/goodsCatUpdate"
     *       ),
     *     ),
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
     *   schema="goodsCatUpdate",
     *   description="广告数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/goodsCatCreate"),
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
     * @OA\Post(path="/spu/category/delete",
     *   summary="分类删除",
     *   tags={"spu模块"},
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
     *
     */
    public function actionDelete()
    {
        $params = $this->queryMapPost();
        if (GoodsCategory::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }
        return GoodsCategory::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }


    /**
     * @OA\Get(path="/spu/category/child",
     *   summary="分类的子类",
     *   tags={"spu模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\Parameter(name="parent_id",in="query",@OA\Schema(type="integer"),description="分类的id 0为顶级分类"),
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
     */
    public function actionChild(){
        $map['parent_id'] = Yii::$app->request->get('parent_id',0);
        if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND && empty($params['user_data'])) {
            $map['status'] = 1;
        }
        $models = GoodsCategory::find()->with(['file'])->where($map)->all();

        $list = [];
        foreach ($models as $item) {
            $row = $item->toArray();
            $row['file'] = $item->file ?? [];
            $list[] = $row;
        }
        return $this->success($list);
    }

    /**
     * @OA\Get(path="/spu/category/tree",
     *   summary="分类树",
     *   tags={"spu模块"},
     *   
     *   @OA\Response(
     *     response=200,
     *     description="列表数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/goodsCatTree"),
     *     ),
     *   ),
     * )
     *
     * @OA\Schema(
     *   schema="goodsCatTree",
     *   description="分类列表结构",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="分类id"),
     *       @OA\Property(property="name", type="string", description="分类名称"),
     *       @OA\Property( property="image", description="售价"),
     *       @OA\Property( property="children", type="array", description="子集", @OA\Items(
     *           @OA\Property(property="name", type="string", description="分类名称"),
     *           @OA\Property( property="image", description="售价"),
     *         )
     *       ),
     *     )
     *   }
     * )
     */
    public function actionTree(){
        $id = Yii::$app->request->get('id');
        $map = [];
        if (!empty($id)) {
            $map = ['!=', 'id', (int)$id];
        }

        $query = GoodsCategory::find()->select(['id', 'parent_id', 'name','image_id'])->with(['file']);
        if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND) {
            $query->andFilterWhere($map);
        }
        $models = $query->all();

        $list = [];
        foreach ($models as $item) {
            $row = $item->toArray();
            $row['file'] = $item->file ?? [];
            $list[] = $row;
        }

        $tree = \bricksasp\base\Tools::build_tree($list);
        return $this->success($tree);
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Category the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = GoodsCategory::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
    }
}
