<?php
namespace bricksasp\pay\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\PaySetting;
use yii\data\ActiveDataProvider;
use bricksasp\models\redis\Token;
use bricksasp\base\BackendController;
use bricksasp\models\form\OrderValidate;

/**
 * PaySettingController implements the CRUD actions for PaySetting model.
 */
class PaySettingController extends BackendController
{
    public function loginAction()
    {
        return [
            'pay-params'
        ];
    }

    /**
     * @OA\Get(path="/pay/pay-setting/index",
     *   summary="支付设置列表",
     *   tags={"pay模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
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
        $params = Yii::$app->request->get();
        $query = PaySetting::find();

        $query->andFilterWhere($this->ownerCondition());

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
     * @OA\Get(path="/pay/pay-setting/view",
     *   summary="支付设置详情",
     *   tags={"pay模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   @OA\Parameter(name="platform",in="query",@OA\Schema(type="integer"),description="1微信2支付宝",example=1),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/PaySettingUpdate"
     *       ),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel($this->updateCondition(['platform'=>$params['platform']]) ?? 0);
        return $this->success($model);
    }

    /**
     * @OA\Post(path="/pay/pay-setting/create",
     *   summary="创建支付设置",
     *   tags={"pay模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/PaySettingCreate"
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
     *   schema="PaySettingCreate",
     *   description="支付设置",
     *   @OA\Property(property="platform", type="integer", description="1微信2支付宝",example=1),
     *   @OA\Property(property="config", type="object", description="开发密钥",
     *     @OA\Property(property="mch_id", type="string", description="商户号id"),
     *     @OA\Property(property="mch_key", type="string", description="商户号密钥"),
     *     @OA\Property(property="ssl_cer", type="string", description="pem证书"),
     *     @OA\Property(property="ssl_key", type="string", description="pem证书密钥"),
     *     @OA\Property(property="notify_url", type="string", description="支付成功回调地址",),
     *     @OA\Property(property="redirect_url", type="string", description="网页支付重定向地址",),
     *   ),
     *   @OA\Property(property="status", type="integer", description="1启用"),
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new PaySetting();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/pay/pay-setting/update",
     *   summary="修改支付设置",
     *   tags={"pay模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/PaySettingUpdate"
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
     *   schema="PaySettingUpdate",
     *   description="支付设置",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/PaySettingCreate"),
     *   }
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        if (!empty($params['id'])) {
            unset($params['id']);
        }
        $model = $this->findModel($this->updateCondition(['platform'=>$params['platform']??0]));

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/pay/pay-setting/delete",
     *   summary="删除支付设置",
     *   tags={"pay模块"},
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
     *
     * 
     */
    public function actionDelete()
    {
        $params = $this->queryMapPost();
        if (PaySetting::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }
        return PaySetting::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }

    protected function findModel($id)
    {
        if (($model = PaySetting::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
    }

    /**
     * @OA\Get(path="/pay/pay-setting/pay-params",
     *   summary="获取支付参数",
     *   tags={"pay模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\Parameter(name="order_id",in="query",required=true,@OA\Schema(type="integer"),description="订单id"),
     *   @OA\Parameter(name="scene",in="query",required=true,@OA\Schema(type="integer"),description="2跑腿"),
     *   @OA\Parameter(name="pay_platform",in="query",required=true,@OA\Schema(type="string",default="2"),description="支付方式 2微信3支付宝"),
     *   @OA\Parameter(name="pay_type",in="query",required=true,@OA\Schema(type="string",default="qr"),description="支付类型 微信 对应(app:app支付,bar:刷卡支付,lite:小程序支付,pub:公众号,qr:扫码支付,wap:H5手机网站支付) 支付宝 对应(app:app支付,bar:刷卡支付,qr:扫码支付,wap:支付宝手机网站支付,web:电脑支付（即时到账）)"),
     *   @OA\Response(
     *     response=200,
     *     description="商品详情结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/payParam"),
     *     ),
     *   ),
     * )
     *
     *
     *
     * @OA\Schema(
     *   schema="payParam",
     *   description="对应支付参数结构"
     * )
     */
    public function actionPayParams() {
        $params = $this->queryMapGet();
        $validator = new OrderValidate($params, ['scenario' => 'create_bill']);
        if ($validator->validate()) {
            $model = new PaySetting();
            return $this->success($model->createOrderPay($this->sysParams($validator->getSaveData())));
        }
        return $this->fail($validator->errors);
    }
}
