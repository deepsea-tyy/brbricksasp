<?php

namespace bricksasp\promotion\controllers;

use Yii;
use bricksasp\spu\models\Goods;
use yii\data\ActiveDataProvider;
use bricksasp\models\redis\Token;
use bricksasp\promotion\models\Promotion;
use bricksasp\promotion\models\PromotionCoupon;
use bricksasp\promotion\models\PromotionCondition;
use yii\db\Expression;

class CouponController extends \bricksasp\base\BackendController
{
    public function checkLoginAction()
    {
        return [
            'index',
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
     * @OA\Get(path="/promotion/coupon/index",
     *   summary="优惠券列表",
     *   tags={"促销模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="相应结构",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(ref="#/components/schemas/promotionList"),
     *     ),
     *   ),
     * )
     *
     * 
     * @OA\Schema(
     *   schema="promotionList",
     *   description="列表结构",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="促销id"),
     *       @OA\Property(property="name", type="string", description="促销名称"),
     *       @OA\Property(property="instruction", type="string", description="使用说明"),
     *       @OA\Property(property="start_at", type="string", description="开始时间"),
     *       @OA\Property(property="end_at", type="string", description="结束时间"),
     *       @OA\Property(property="code", type="string", description="促销调用代码"),
     *       @OA\Property(property="exclusion", type="integer", description="排他'1是2否"),
     *       @OA\Property(property="receive_status", type="integer", description="领取状态" ),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/PromotionCondition"),
     *   }
     * )
     */
    public function actionIndex()
    {
        $map = [
            'and',
            ['owner_id'=>$this->current_owner_id, 'type' => Promotion::TYPE_COUPON, 'status' => Promotion::STATUS_YES],
            ['and', ['>=', 'end_at', time()], ['<=', 'start_at', time()] ]
        ];
        $field = ['id','name','instruction','code','start_at','end_at','exclusion','receive_num','participant_num','num'];
        $list = Promotion::find()->select($field)->with(['condition'])->where($map)->asArray()->all();
        $userCoupon = [];
        if ($this->current_user_id) {
            $userCoupon = PromotionCoupon::find()->select(['promotion_id'])->where(['owner_id'=>$this->current_owner_id, 'user_id'=>$this->current_user_id])->asArray()->all();
            $userCoupon = array_column($userCoupon, 'promotion_id');
        }
        foreach ($list as &$v) {
            $v['receive_status'] = in_array($v['id'], $userCoupon) ? 1 : 0;
        }

        return $this->success($list);
    }

    /**
     * @OA\Get(path="/promotion/coupon/record",
     *   summary="优惠券使用记录列表",
     *   tags={"促销模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *
     *   @OA\Response(
     *     response=200,
     *     description="相应结构",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *      @OA\Schema(ref="#/components/schemas/promotionUseList"),
     *     ),
     *   ),
     * )
     *
     *
     *  @OA\Schema(
     *   schema="promotionUseList",
     *   title="修改商铺数据结构",
     *   description="促销模块",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="name", type="string", description="优惠券名称"),
     *       @OA\Property(property="order.created_at", type="integer", description="下单时间"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/promotionList"),
     *   }
     * )
     *
     */
    public function actionRecord(){
        $params = $this->queryMapGet();
        $with = ['order','userInfo','promotion','condition'];

        $models = PromotionCoupon::find()->with($with)->where($this->ownerCondition())->all();

        $list = [];

        if (!empty($with)) {
            foreach ($models as $item) {
                $r = $item->toArray();
                foreach ($with as $v) {
                    $r[$v] = $item->$v;
                }
                if($r['condition']){
                    $r['condition_type_name'] = PromotionCondition::TYPE_NAME[$r['condition_type']];
                    $r['result_type_name'] = PromotionCondition::RESULT_TYPE_NAME[$r['result_type']];
                }
                $list[] = $r;
            }
        }

        return $this->success([
            'list' => $list ? $list : $models,
            'pageCount' => $dataProvider->pagination->pageCount,
            'totalCount' => $dataProvider->pagination->totalCount,
            'page' => $dataProvider->pagination->page + 1,
            'pageSize' => $dataProvider->pagination->limit,
        ]);
    }

    /**
     * @OA\Get(path="/promotion/coupon/receive",
     *   summary="领取优惠券",
     *   tags={"促销模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   @OA\Parameter(name="promotion_id",in="query",required=true,@OA\Schema(type="integer"),description="促销id"),
     *   @OA\Response(
     *     response=200,
     *     description="相应结构",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *           @OA\Property( property="id", type="integer", description="优惠券id" ),
     *       ),
     *     ),
     *   ),
     * )
     *
     */
    public function actionReceive()
    {
        $model = new Promotion();
        $coupon = $model->receiveCoupon($this->queryMapGet());
        return $coupon ? $this->success(['id' => $coupon->id]) : $this->fail();
    }

    /**
     * @OA\Get(path="/promotion/coupon/get-coupon-goods",
     *   summary="优惠券对应的商品",
     *   tags={"促销模块"},
     *   @OA\Parameter(name="id",in="query",required=true,@OA\Schema(type="integer"),description="商品id"),
     *   @OA\Response(
     *     response=200,
     *     description="相应结构",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionGetCouponGoods(){
        $params = $this->queryMapGet();
        $condition = PromotionCondition::findOne(['promotion_id'=>$params['id']]);
        if(!$condition){
            return $this->fail('未找到该优惠券信息');
        }
        $map = [];
        if($condition['condition_type'] == PromotionCondition::TYPE_ALL || $condition['condition_type'] == PromotionCondition::TYPE_REDUCTION){
            $map = [];
        }
        if($condition['condition_type'] == PromotionCondition::TYPE_CAT){
            $map = ['cat_id'=>explode(',',$condition['content'])];
        }
        if($condition['condition_type'] == PromotionCondition::TYPE_PART){
            $map = ['id'=>explode(',',$condition['content'])];
        }
        $dataProvider = new ActiveDataProvider([
            'query' => Goods::find()->with(['productItems'])->where($map)->asArray(),
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
     * @OA\Get(path="/promotion/coupon/user-coupon",
     *   summary="用户已领取优惠券列表",
     *   tags={"促销模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   @OA\Response(
     *     response=200,
     *     description="相应结构",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(ref="#/components/schemas/couponList"),
     *     ),
     *   ),
     * )
     *
     * @OA\Schema(
     *   schema="couponList",
     *   description="列表结构",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property( property="id", type="integer", description="优惠券id" ),
     *       @OA\Property(property="code", type="string", description="优惠券代码"),
     *       @OA\Property(property="status", type="integer", description="使用状态'1正常2已使用"),
     *       @OA\Property( property="start_at", type="integer", description="开始时间"),
     *       @OA\Property( property="end_at", type="integer", description="结束时间" ),
     *       @OA\Property( property="type", type="integer", description="类型：1商品减固定金额2商品折扣3商品一口价4订单减固定金额5订单折扣6订单一口价" ),
     *       @OA\Property(property="content", type="string", description="type对应值"),
     *       @OA\Property(property="exclusion", type="integer", description="是否可同时使用 2是"),
     *     )
     *   }
     * )
     */
    public function actionUserCoupon()
    {
        $params = $this->queryMapGet();
        $field = ['promotion_id', 'id', 'code', 'status', 'start_at', 'end_at','owner_id'];
        $with = ['promotion', 'condition','store'];
        $promotionCoupon = new PromotionCoupon();
        $orderby = ['created_at'=>SORT_DESC];
        //未使用优惠券列表
        $data = $promotionCoupon->couponList($params,$field,$with,PromotionCoupon::STATUS_NO,$orderby);
        $result['coupon_list'] = $data;
        //已使用优惠券列表
        $orderUsed = ['use_at'=>SORT_DESC];
        $used = $promotionCoupon->couponList($params,$field,$with,PromotionCoupon::STATUS_USED,$orderUsed);
        $result['used_list'] = $used;
        return $this->success($result);
    }

    /**
     * @OA\Get(path="/promotion/coupon/goods",
     *   summary="商品优惠券",
     *   tags={"促销模块"},
     *   @OA\Parameter(name="id",in="query",required=true,@OA\Schema(type="integer"),description="商品id"),
     *   @OA\Response(
     *     response=200,
     *     description="相应结构",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(ref="#/components/schemas/couponList"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionGoods()
    {
        $params = $this->queryMapGet();

        $map = [
            'and',
            ['owner_id'=>$this->current_owner_id, 'type' => Promotion::TYPE_COUPON, 'status' => [Promotion::COUPON_STATUS_YES,Promotion::COUPON_STATUS_DISPLAY]],
            ['and', ['>=', 'end_at', time()], ['<=', 'start_at', time()] ]
        ];
        $promotions = Promotion::find()->select(['id'])->with(['condition'])->where($map)->asArray()->all();

        if (empty($promotions)) {
            return $this->success([]);
        }

        $map = [
            'and',
            ['promotion_id' => array_column($promotions,'id')],
            [
                'or',
                ['condition_type'=>PromotionCondition::TYPE_ALL],
                ['and', ['condition_type'=>PromotionCondition::TYPE_PART], new Expression('FIND_IN_SET(' . $params['id'] . ',content)')]
            ]
        ];
        $res = PromotionCondition::find()->select(['promotion_id','result_type','result'])->with(['promotion'])->where($map)->asArray()->all();
        $userCoupon = [];
        if ($this->current_user_id) {
            $userCoupon = PromotionCoupon::find()->select(['id', 'promotion_id'])->where(['owner_id'=>$this->current_owner_id, 'user_id'=>$this->current_user_id])->asArray()->all();
            $userCoupon = array_column($userCoupon,'id','promotion_id');
        }
        foreach ($res as &$v) {
            $v['receive_status'] = array_key_exists($v['promotion_id'], $userCoupon) ? '1' : '0';
            $v['coupon_id'] = $v['receive_status'] ? $userCoupon[$v['promotion_id']] : '0';
        }
        return $this->success($res);
    }

    /**
     * @OA\Get(path="/promotion/coupon/code",
     *   summary="代码获取促销信息",
     *   tags={"促销模块"},
     *   @OA\Parameter(name="code",in="query",required=true,@OA\Schema(type="string",description="促销代码",default="default_1")),
     *   @OA\Response(
     *     response=200,
     *     description="相应结构",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(ref="#/components/schemas/couponList"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionCode()
    {
        $params = $this->queryMapGet();
        $code = array_filter(explode(',', $params['code']));
        $data = Promotion::find()->select(['id', 'name', 'instruction', 'type', 'code', 'start_at', 'end_at', 'exclusion'])->with(['condition'])->where(['owner_id'=>$this->current_owner_id, 'type' => Promotion::TYPE_COUPON, 'code' => $code])->asArray()->all();

        $userCoupon =[];
        if ($this->current_user_id) {
            $userCoupon = PromotionCoupon::find()->select(['promotion_id'])->where(['owner_id'=>$this->current_owner_id, 'user_id'=>$this->current_user_id])->asArray()->all();
            $userCoupon = array_column($userCoupon, 'promotion_id');
        }
        foreach ($data as &$v) {
            $v['receive_status'] = in_array($v['id'], $userCoupon) ? 1 : 0;
        }
        return $this->success($data);
    }

    
}
