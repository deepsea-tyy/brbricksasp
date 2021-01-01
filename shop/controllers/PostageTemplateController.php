<?php

namespace bricksasp\shop\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use bricksasp\base\Tools;
use bricksasp\base\BackendController;
use bricksasp\models\PostageTplArea;
use bricksasp\models\PostageTemplate;

/**
 * PostageTemplateController implements the CRUD actions for PostageTemplate model.
 */
class PostageTemplateController extends BackendController
{
    public function noLoginAction()
    {
        return [];
    }

    /**
     * @OA\Get(path="/shop/postage-template/index",
     *   summary="运费模板列表",
     *   tags={"shop模块"},
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
        $query =  PostageTemplate::find();
        $query->andFilterWhere([
            'status' => $params['status']??null,
        ]);
        $query->andFilterWhere($this->ownerCondition());
        $query->andFilterWhere(['like', 'name', $params['name']??null]);
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
     * @OA\Get(path="/shop/postage-template/view",
     *   summary="运费模板详情",
     *   tags={"shop模块"},
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
     *       @OA\Schema(ref="#/components/schemas/postageTemplateUpdate"),
     *     ),
     *   ),
     * )
     *
     * @OA\Schema(
     *   schema="/postageTemplateView",
     *   description="运费模板数据详情",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="labelItems", type="array", description="封面", @OA\Items(ref="#/components/schemas/label")),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/postageTemplateUpdate"),
     *   }
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel($params['id']??0);
        $data = $model->toArray();
        $tplArea = [];
        if ($model->tplArea) {
            foreach ($model->tplArea as $item) {
                $item->area_id = $item->area_id ? json_decode($item->area_id,true) : [];
                $tplArea[] = $item;
            }
        }
        $data['tplArea'] = $tplArea;
        return $this->success($data);
    }

    /**
     * @OA\Post(path="/shop/postage-template/create",
     *   summary="创建运费模板",
     *   tags={"shop模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/postageTemplateCreate"
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
     *   schema="postageTemplateCreate",
     *   description="运费模板",
     *   @OA\Property(property="name", type="string", description="配送方式名称"),
     *   @OA\Property(property="is_default", type="integer", description="1默认"),
     *   @OA\Property(property="billing_plan", type="integer", description="1按重量计费2按件计费"),
     *   @OA\Property(property="logistics_id", type="integer", description="物流公司id",),
     *   @OA\Property(property="special_type", type="integer", description="1不配送区域2只配送区域",),
     *   @OA\Property(property="special_status", type="integer", description="1启用",),
     *   @OA\Property(property="special_area_id", type="string", description="特殊区域id",),
     *   @OA\Property(property="tpl_area_items", type="array", description="区域运费设置", @OA\Items(
     *       @OA\Property(property="postage_id",type="integer",description="运费模版id 新建不用填写"),
     *       @OA\Property(property="first",type="string",description="首重/件"),
     *       @OA\Property(property="first_price",type="number",description="首重/件价格"),
     *       @OA\Property(property="renew",type="integer",description="续重/件"),
     *       @OA\Property(property="renew_price",type="number",description="续重/件价格"),
     *       @OA\Property(property="full_price",type="number",description="邮费满额包邮价"),
     *       @OA\Property(property="area_id",type="array",description="区域id", @OA\Items(
     *           @OA\Property(property="id",type="integer",description="区域id"),
     *           @OA\Property(property="name",type="integer",description="区域名称"),
     *         )
     *       ),
     *     ),
     *   ),
     *   
     *   required={"name"}
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new PostageTemplate();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/shop/postage-template/update",
     *   summary="修改运费模板",
     *   tags={"shop模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/postageTemplateUpdate"
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
     *   schema="postageTemplateUpdate",
     *   description="运费模板数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/postageTemplateCreate"),
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
     * @OA\Post(path="/shop/postage-template/delete",
     *   summary="删除运费模板",
     *   tags={"shop模块"},
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
        if (PostageTemplate::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }
        $transaction = PostageTemplate::getDb()->beginTransaction();
        try {
            PostageTemplate::deleteAll($this->updateCondition(['id'=>$params['ids']??0]));
            PostageTplArea::deleteAll(['postage_id'=>$params['ids']??0]);
            $transaction->commit();
            return $this->success();
        } catch(\Throwable $e) {
            $transaction->rollBack();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Finds the PostageTemplate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PostageTemplate the loaded model
     */
    protected function findModel($id)
    {
        if (($model = PostageTemplate::findOne($id)) !== null) {
            return $model;
        }

        Tools::breakOff(40001);
    }
}
