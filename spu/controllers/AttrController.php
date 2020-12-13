<?php
namespace bricksasp\spu\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use bricksasp\base\BackendController;
use bricksasp\spu\models\GoodsAttr;

/**
 * AttrController implements the CRUD actions for Spec model.
 */
class AttrController extends BackendController
{
    /**
     * @OA\Get(path="/spu/attr/index",
     *   summary="属性/参数列表",
     *   tags={"spu模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
     *   @OA\Parameter(name="type",in="query",@OA\Schema(type="integer"),description="1属性2参数",example="1"),
     *   @OA\Parameter(name="is_delete",in="query",@OA\Schema(type="integer"),description="1软删除"),
     *
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/goodsAttrUpdate"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->get();
        $query = GoodsAttr::find();
        $query->andFilterWhere([
            'type' => $params['type']??GoodsAttr::TYPE_ATTR,
        ]);
        
        $query->andFilterWhere($this->ownerCondition());
        $query->andFilterWhere(['like', 'name', $params['name']??null]);
        $query->andFilterWhere(['is_delete'=> empty($params['is_delete']) ? 0 : 1]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $list = [];
        foreach ($dataProvider->models as $item) {
            $row = $item->toArray();
            $row['value'] = json_decode(trim($item->value, '"'),true) ?? [];
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
     * @OA\Get(path="/spu/attr/view",
     *   summary="属性/参数详情",
     *   tags={"spu模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="id"),
     *
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/goodsAttrUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $model = $this->findModel(Yii::$app->request->get('id'));
        $data = $model->toArray();
        $data['file'] = $model->file ?? [];
        return $this->success($data);
    }

    /**
     * @OA\post(path="/spu/attr/create",
     *   summary="添加属性/参数",
     *   tags={"spu模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/goodsAttrCreate"
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
     *   schema="goodsAttrCreate",
     *   description="属性/参数",
     *   @OA\Property(property="name", type="string", description="属性/参数名称"),
     *   @OA\Property(property="type", type="integer", description="类型1属性2参数", example="1"),
     *   @OA\Property(property="text_type", type="string", description="checkbox|radio|text"),
     *   @OA\Property(property="sort", type="integer", description="排序"),
     *   @OA\Property(property="value", type="array", description="属性/参数值", items={}),
     *   required={"name"}
     * )
     *
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new GoodsAttr();

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\post(path="/spu/attr/update",
     *   summary="修改属性/参数",
     *   tags={"spu模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/goodsAttrUpdate"
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
     *   schema="goodsAttrUpdate",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/goodsAttrCreate"),
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
     * @OA\Post(path="/spu/attr/delete",
     *   summary="删除属性/参数",
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
     */
    public function actionDelete()
    {
        $params = $this->queryMapPost();
        if (GoodsAttr::updateAll(['is_delete'=>1],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }
        return GoodsAttr::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }

    /**
     * Finds the GoodsAttr model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return GoodsAttr the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = GoodsAttr::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
    }
}
