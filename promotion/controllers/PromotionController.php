<?php

namespace bricksasp\promotion\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\spu\models\Goods;
use bricksasp\base\BackendController;
use bricksasp\models\redis\Token;
use bricksasp\promotion\models\Promotion;
use bricksasp\promotion\models\PromotionCoupon;
use bricksasp\promotion\models\PromotionCondition;
use yii\data\ActiveDataProvider;

/**
 * PromotionController implements the CRUD actions for Promotion model.
 */
class PromotionController extends BackendController
{
    public function loginAction()
    {
        return [
            'index',
            'coupon',
            'view'
        ];
    }

    public function noLoginAction()
    {
        return [
            'index',
            'view'
        ];
    }

    /**
     * @OA\Get(path="/promotion/promotion/index",
     *   summary="促销列表",
     *   tags={"促销模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
     *   @OA\Parameter(name="is_delete",in="query",@OA\Schema(type="integer"),description="1软删除"),
     *   @OA\Parameter(name="status",in="query",@OA\Schema(type="integer"),description="1显示0不显示"),
     *   @OA\Parameter(name="type",in="query",@OA\Schema(type="integer"),description="1优惠券2促销3团购4秒杀"),
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
    public function actionIndex()
    {
        if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND) {
            $with = ['condition'];
            $fields = ['id', 'name', 'instruction', 'num', 'type', 'participant_num', 'code', 'start_at', 'end_at','receive_num','status'];
        }else{
            $with=$fields=[];
        }

        $params = Yii::$app->request->get();
        $query = Promotion::find()->select($fields);
        $query->andFilterWhere($this->ownerCondition());

        if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND) {
            $query->andFilterWhere(['status'=> empty($params['status']) ? 1 : 0]);
        }
        $query->andFilterWhere(['is_delete'=> empty($params['is_delete']) ? 0 : 1]);
        $query->andFilterWhere(['type'=> $params['type']??1]);
        $query->andFilterWhere(['like', 'name', $params['name']??null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $list = [];
        foreach ($dataProvider->models as $item) {
            $r = $item->toArray();
            foreach ($with as $v) {
                $r[$v] = $item->$v;
            }
            if ($with) {
                if($r['condition']){
                    $r['condition']['condition_type_name'] = PromotionCondition::CONDITION_NAME[$r['condition']['condition_type']]??'';
                    $r['condition']['result_type_name'] = PromotionCondition::RESULT_NAME[$r['condition']['result_type']]??'';
                }

            }
            $list[] = $r;
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
     * @OA\Get(path="/promotion/promotion/coupon",
     *   summary="促销优惠券列表",
     *   tags={"促销模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
     *   @OA\Parameter(name="status",in="query",@OA\Schema(type="integer"),description="0未使用1已使用"),
     *   @OA\Parameter(name="promotion_id",in="query",@OA\Schema(type="integer"),description="promotion_id"),
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
    public function actionCoupon()
    {
        $with[] = 'promotion';
        $with[] = 'condition';

        $params = Yii::$app->request->get();
        $query = PromotionCoupon::find()->with($with);
        $query->andFilterWhere(['promotion_id'=> $params['promotion_id']??0]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $list = [];
        foreach ($dataProvider->models as $item) {
            $r = $item->toArray();
            foreach ($with as $v) {
                $r[$v] = $item->$v;
            }
            $list[] = $r;
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
     * @OA\Get(path="/promotion/promotion/view",
     *   summary="促销详情",
     *   tags={"促销模块"},
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
     *       @OA\Schema(ref="#/components/schemas/promotionUpdate"),
     *     ),
     *   ),
     * )
     *
     * 
     * 
     * @OA\Schema(
     *   schema="promotionView",
     *   description="促销数据详情",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="participant_num", type="integer", description="已参与人数"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/promotionUpdate"),
     *   }
     * )
     */
    public function actionView()
    {
        $model = $this->findModel(Yii::$app->request->get('id'));
        $data = $model->toArray();
        $data['condition'] = $model->condition;
        return $this->success($data);
    }

    /**
     * @OA\Post(path="/promotion/promotion/create",
     *   summary="创建促销",
     *   tags={"促销模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/promotionCreate"
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
     *   schema="promotionCreate",
     *   allOf={
     *     @OA\Schema(
     *       description="促销",
     *       @OA\Property(property="name", type="string", description="促销名称"),
     *       @OA\Property(property="num", type="integer", description="促销/优惠券发放数量", example="100"),
     *       @OA\Property(property="receive_num", type="integer", description="限领次数", example="1"),
     *       @OA\Property(property="participant_num", type="integer", description="参与人数"),
     *       @OA\Property(property="instruction", type="string", description="使用说明"),
     *       @OA\Property(property="scene", type="integer", description="使用场景 1:默认2:其他", example="1"),
     *       @OA\Property(property="type", type="integer", description="类型 1优惠券2促销3团购4秒杀", example="1"),
     *       @OA\Property(property="code", type="string", description="调用代码"),
     *       @OA\Property(property="start_at", type="string", description="开始时间"),
     *       @OA\Property(property="end_at", type="string", description="结束时间"),
     *       @OA\Property(property="exclusion", type="integer", description="排他1是2否"),
     *       @OA\Property(property="sort", type="integer", description="排序/权重"),
     *       @OA\Property(property="status", type="integer", description="1显示"),
     *       required={"name", "instruction"}
     *     ),
     *     @OA\Schema(ref="#/components/schemas/PromotionCondition"),
     *   }
     * )
     * 
     * @OA\Schema(
     *   schema="PromotionCondition",
     *   description="促销规则",
     *   @OA\Property(property="condition", type="object", description="促销规则",
     *     @OA\Property(property="condition_type", type="integer", description="达成条件类型 1最少金额2最少数量"),
     *     @OA\Property(property="condition", type="string", description="达成条件"),
     *     @OA\Property(property="content_type", type="integer", description="促销类型 1商品分类2指定商品3指定单品4订单满减"),
     *     @OA\Property(property="content", type="string", description="促销内容"),
     *     @OA\Property(property="result_type", type="integer", description="促销结果 1商品减固定金额2商品折扣3商品一口价4订单减固定金额5订单折扣6订单一口价"),
     *     @OA\Property(property="result", type="string", description="促销结果"),
     *   ),
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new Promotion();

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/promotion/promotion/update",
     *   summary="修改促销",
     *   tags={"促销模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/promotionUpdate"
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
     *   schema="promotionUpdate",
     *   description="促销数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/promotionCreate"),
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
     * @OA\Post(path="/promotion/promotion/delete",
     *   summary="删除促销",
     *   tags={"促销模块"},
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
        if (Promotion::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }

        PromotionCondition::deleteAll(['promotion_id'=> $params['ids']??0]);
        return Promotion::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }

    /**
     * Finds the Promotion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Promotion the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Promotion::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
    }
}
