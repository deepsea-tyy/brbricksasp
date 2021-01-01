<?php

namespace bricksasp\shop\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\Store;
use bricksasp\models\Company;
use yii\data\ActiveDataProvider;
use bricksasp\base\BackendController;
use bricksasp\models\CompanyQualifications;

/**
 * StoreController implements the CRUD actions for Store model.
 */
class StoreController extends BackendController
{
    public function noLoginAction()
    {
        return [];
    }

    /**
     * @OA\Get(path="/shop/store/index",
     *   summary="商铺列表",
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
        $query =  Store::find();
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
     * @OA\Get(path="/shop/store/view",
     *   summary="商铺详情",
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
     *       @OA\Schema(ref="#/components/schemas/StoreUpdate"),
     *     ),
     *   ),
     * )
     *
     * @OA\Schema(
     *   schema="/StoreView",
     *   description="商铺数据详情",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="labelItems", type="array", description="封面", @OA\Items(ref="#/components/schemas/label")),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/StoreUpdate"),
     *   }
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel($this->updateCondition(empty($params['id']) ? [] : ['owner_id'=> $params['id']]));
        $data = $model->toArray();
        
        if ($model->company) {
            $data['company'] = $model->company->toArray();
            $data['company']['gps'] = json_decode($model->company->gps,true);
        }
        $data['companyQualifications'] = $model->companyQualifications;
        return $this->success($data);
    }

    /**
     * @OA\Post(path="/shop/store/create",
     *   summary="创建商铺",
     *   tags={"shop模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/StoreCreate"
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
     *   schema="StoreCreate",
     *   description="商铺",
     *   @OA\Property(property="name", type="string", description="店铺名称"),
     *   @OA\Property(property="industry_id", type="integer", description="行业分类id"),
     *   @OA\Property(property="type", type="integer", description="1商铺2供货商3学校"),
     *   @OA\Property(property="nature", type="integer", description="经营性质1旗舰店2专营店3专卖店",),
     *   @OA\Property(property="brand", type="string", description="经营品牌",),
     *   @OA\Property(property="logo", type="string", description="店铺logo",),
     *   @OA\Property(property="brief", type="string", description="品牌/店铺简介",),
     *   @OA\Property(property="contacts", type="string", description="联系人",),
     *   @OA\Property(property="contacts_phone", type="string", description="联系人电话",),
     *   @OA\Property(property="return_area_id", type="integer", description="退货所在地区",),
     *   @OA\Property(property="return_address", type="string", description="退货详细地址",),
     *   @OA\Property(property="tm_url", type="string", description="天猫店铺链接",),
     *   @OA\Property(property="jd_url", type="string", description="京东店铺链接",),
     *   @OA\Property(property="own_site_url", type="string", description="自营网站",),
     *   @OA\Property(property="area_id", type="integer", description="店铺所在地区",),
     *   @OA\Property(property="address", type="string", description="店铺详细地址",),
     *   @OA\Property(property="status", type="integer", description="0未审核1通过2拒绝",),
     *   @OA\Property(property="refuse_reasons", type="string", description="拒绝原因",),
     *   @OA\Property(property="is_delete", type="integer", description="",),
     *   @OA\Property(property="companyItem", type="object", description="公司基本信息",
     *     @OA\Property(property="id", type="string", description="",),
     *     @OA\Property(property="name", type="string", description="公司名称",),
     *     @OA\Property(property="website", type="string", description="官网",),
     *     @OA\Property(property="logo", type="string", description="公司logo",),
     *     @OA\Property(property="gps", type="object", description="坐标",
     *       @OA\Property(property="type", type="string", description="json格式1百度2高德3腾讯4百度",),
     *       @OA\Property(property="lat", type="string", description="",),
     *       @OA\Property(property="lon", type="string", description="",),
     *     ),
     *     @OA\Property(property="level", type="integer", description="0默认等级",),
     *     @OA\Property(property="mark", type="string", description="备注",),
     *     @OA\Property(property="area_id", type="integer", description="所在地区",),
     *     @OA\Property(property="address", type="string", description="详细地址",),
     *     @OA\Property(property="status", type="integer", description="0待审核1审核通过2审核不能过",),
     *     @OA\Property(property="review_user_id", type="integer", description="审核人userid",),
     *     @OA\Property(property="review_at", type="integer", description="审核人时间",),
     *     @OA\Property(property="body", type="integer", description="主体1境内企业",),
     *     @OA\Property(property="main_products", type="string", description="主营商品",),
     *     @OA\Property(property="main_model", type="integer", description="主营模式1生产厂家",),
     *     @OA\Property(property="director", type="string", description="负责人",),
     *     @OA\Property(property="director_mail", type="string", description="负责人邮箱",),
     *     @OA\Property(property="director_mobile", type="string", description="负责人手机",),
     *     @OA\Property(property="operations_contact", type="string", description="运营联系人",),
     *     @OA\Property(property="operations_contact_mobile", type="string", description="运营联系人手机",),
     *     @OA\Property(property="operations_contact_mail", type="string", description="运营联系人邮箱",),
     *   ),
     *   @OA\Property(property="qualificationsItem", type="object", description="公司资质",
     *     @OA\Property(property="id", type="integer", description="",),
     *     @OA\Property(property="type", type="integer", description="企业类型1有限责任公司",),
     *     @OA\Property(property="full_address", type="string", description="公司地址",),
     *     @OA\Property(property="incorporation_at", type="string", description="公司成立日期",),
     *     @OA\Property(property="registered_capital", type="number", description="注册资本",),
     *     @OA\Property(property="business_scope", type="string", description="经营范围",),
     *     @OA\Property(property="credit_code", type="string", description="社会统一信用代码",),
     *     @OA\Property(property="business_license", type="string", description="营业执照扫描件",),
     *     @OA\Property(property="business_license_duration", type="string", description="开始时间戳,结束时间戳",),
     *     @OA\Property(property="three_to_one", type="integer", description="1三证合一",),
     *     @OA\Property(property="tax_type", type="integer", description="1小规模纳税人",),
     *     @OA\Property(property="legal_rep", type="string", description="法人代表",),
     *     @OA\Property(property="legal_rep_frontal_photo", type="string", description="法人身份证正面照",),
     *     @OA\Property(property="legal_rep_reverse_photo", type="string", description="法人身份证反面照",),
     *     @OA\Property(property="trademark_name", type="string", description="商标名称",),
     *     @OA\Property(property="trademark_license", type="string", description="商标证书",),
     *     @OA\Property(property="trademark_license_duration", type="string", description="商标有效时间",),
     *     @OA\Property(property="trademark_license_change", type="string", description="商标变更/续展证明",),
     *     @OA\Property(property="brand_permit", type="string", description="品牌授权书",),
     *     @OA\Property(property="purchase_sale_voucher", type="string", description="购销凭证",),
     *     @OA\Property(property="administrative_license", type="string", description="行政许可证",),
     *     @OA\Property(property="trademark_inspection_report", type="string", description="检查报告",),
     *     @OA\Property(property="trademark_inspection_report_duration", type="string", description="",),
     *     @OA\Property(property="food_business_license", type="string", description="食品经营许可证",),
     *     @OA\Property(property="food_business_license_duration", type="string", description="",),
     *     @OA\Property(property="food_inspection_report", type="string", description="食品检测报告",),
     *     @OA\Property(property="food_inspection_report_duration", type="string", description="",),
     *   ),
     *   
     *   required={"name"}
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new Store();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/shop/store/update",
     *   summary="修改商铺",
     *   tags={"shop模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/StoreUpdate"
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
     *   schema="StoreUpdate",
     *   description="商铺数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="owner_id", type="integer", description="owner_id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/StoreCreate"),
     *   }
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel($this->updateCondition(empty($params['id']) ? [] : ['owner_id'=> $params['id']]));

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/shop/store/delete",
     *   summary="删除商铺",
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
        if (Store::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['owner_id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }
        $transaction = Store::getDb()->beginTransaction();
        try {
            Store::deleteAll($this->updateCondition(['owner_id'=>$params['ids']??0]));
            Company::deleteAll(['owner_id'=>$params['ids']??0]);
            CompanyQualifications::deleteAll(['owner_id'=>$params['ids']??0]);
            $transaction->commit();
            return $this->success();
        } catch(\Throwable $e) {
            $transaction->rollBack();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Finds the Store model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Store the loaded model
     */
    protected function findModel($id)
    {
        if (($model = Store::findOne($id)) !== null) {
            return $model;
        }

        Tools::breakOff(40001);
    }
}
