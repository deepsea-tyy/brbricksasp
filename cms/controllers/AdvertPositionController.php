<?php
namespace bricksasp\cms\controllers;

use Yii;
use bricksasp\base\Tools;
use yii\data\ActiveDataProvider;
use bricksasp\base\BackendController;
use bricksasp\cms\models\AdvertRelation;
use bricksasp\cms\models\AdvertPosition;

/**
 * AdvertPositionController implements the CRUD actions for AdvertPosition model.
 */
class AdvertPositionController extends BackendController
{
    public function noLoginAction()
    {
        return ['detail'];
    }

    /**
     * @OA\Get(path="/cms/advert-position/index",
     *   summary="广告位列表",
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
        $query =  AdvertPosition::find();
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
     * @OA\Get(path="/cms/advert-position/view",
     *   summary="广告位详情",
     *   tags={"cms模块"},
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
     *       @OA\Schema(ref="#/components/schemas/advert-positionUpdate"),
     *     ),
     *   ),
     * )
     *
     * @OA\Schema(
     *   schema="advert-positionView",
     *   description="广告位数据详情",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="labelItems", type="array", description="封面", @OA\Items(ref="#/components/schemas/label")),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/advert-positionUpdate"),
     *   }
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel($params['id']??0);
        $data = $model->toArray();
        $advert = $model->advertItems ? AdvertPosition::sortItem([$model->advertItems, 'id'],[$model->advertRelation, 'sort', 'advert_id']):[];
        $data['advert'] = array_column($advert,'id');
        return $this->success($data);
    }

    /**
     * @OA\Post(path="/cms/advert-position/create",
     *   summary="创建广告位",
     *   tags={"cms模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/advert-positionCreate"
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
     *   schema="advert-positionCreate",
     *   description="广告位",
     *   @OA\Property(property="name", type="string", description="幻灯片名称"),
     *   @OA\Property(property="code", type="string", description="广告位置编码"),
     *   @OA\Property(property="is_delete", type="integer", description="0正常  1删除"),
     *   @OA\Property(property="advert", type="array", description="广告", items={}),
     *   required={"name"}
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new AdvertPosition();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/cms/advert-position/update",
     *   summary="修改广告位",
     *   tags={"cms模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/advert-positionUpdate"
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
     *   schema="advert-positionUpdate",
     *   description="广告位数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/advert-positionCreate"),
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
     * @OA\Post(path="/cms/advert-position/delete",
     *   summary="删除广告位",
     *   tags={"cms模块"},
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
        if (AdvertPosition::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }
        $transaction = AdvertPosition::getDb()->beginTransaction();
        try {
            AdvertPosition::deleteAll($this->updateCondition(['id'=>$params['ids']??0]));
            AdvertRelation::deleteAll(['position_id'=>$params['ids']??0]);
            $transaction->commit();
            return $this->success();
        } catch(\Throwable $e) {
            $transaction->rollBack();
        }
        return $this->fail();
    }

    /**
     * @OA\Get(path="/cms/advert-position/detail",
     *   summary="广告位广告详情",
     *   tags={"cms模块"},
     *   
     *   @OA\Parameter(name="code",in="query",required=true,@OA\Schema(type="string",description="广告位调用代码",default="home_banner")),
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
        $params = $this->queryMapGet();
        $model = new AdvertPosition();
        return $this->success($model->detail($params));
    }

    /**
     * Finds the AdvertPosition model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AdvertPosition the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AdvertPosition::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
    }
}
