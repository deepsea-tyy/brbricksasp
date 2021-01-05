<?php

namespace bricksasp\wechat\controllers;

use Yii;
use WeChat\Template;
use bricksasp\models\pay\Wechat;
use bricksasp\models\MessageTemplate;

/**
 * 微信服务号微信模板消息接
 * 编号 
 * 交易提醒
 * OPENTM414905257 订单取消提醒  通用
 * OPENTM411352957 订单催付提醒  30min未付款提醒
 * OPENTM405617234 订单核销提醒  线下提货成功
 * OPENTM200565278 退款成功提醒 
 * 
 * OPENTM414564417 付款成功提醒  分销下级付款成功通知上级
 * OPENTM207185188 付款成功提醒  通用
 * 
 * OPENTM418001469 订单自提提醒  
 * OPENTM414956350 订单发货提醒  
 * OPENTM415747405 售后状态提醒  
 * OPENTM405637175 收益到账提醒
 * 
 * OPENTM405485000 提现申请提醒  平台接收
 * 
 * OPENTM418116804 商户新订单支付成功提醒  商家接收
 *
 * 营销提醒
 * OPENTM406099059 成为分销商提醒  
 * OPENTM412358456 新增下级/合作伙伴加入提醒
 *                 用户召回提醒  定时判断活跃时间 
 *                 优惠券发送提醒   选择用户发送
 *                 
 * 平台
 * OPENTM412922431 库存不足通知 

1 
2 售后申请提醒平台
3 

 */
class TplController extends \bricksasp\base\BackendController
{
    /**
     * @OA\Get(path="/wechat/tpl/index",
     *   summary="公众号模板列表",
     *   tags={"微信消息模板"},
     *   @OA\Parameter(name="access-token",in="header",required=true,@OA\Schema(type="string"),description="登录凭证"),
     *   @OA\Parameter(name="scene",in="query",@OA\Schema(type="integer"),description="1默认官方官网 2校园跑腿用户端 3校园跑腿骑手端 4其他"),
     *   @OA\Response(
     *     response=200,
     *     description="响应",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     * 
     *
     */
    public function actionIndex()
    {
        // $a = MessageTemplate::syssend(10320,'TEMP_ORDER_PAID',[
        //     ['value' => '16055922642064'],
        //     ['value' => '2020年11月30日 18:36'],
        //     ['value' => '8.00'],
        //     ['value' => '微信支付'],
        // ]);
        // return $this->success($a);
        $model = $this->getModel(Yii::$app->request->get('scene'));
    	$data = $model->getAllPrivateTemplate();
        return $this->success($data['template_list']);
    }

    /**
     * @OA\Get(path="/wechat/tpl/industry",
     *   summary="获取公众号设置的行业信息",
     *   tags={"微信消息模板"},
     *   @OA\Parameter(name="access-token",in="header",required=true,@OA\Schema(type="string"),description="登录凭证"),
     *   @OA\Parameter(name="scene",in="query",@OA\Schema(type="integer"),description="1默认官方官网 2校园跑腿用户端 3校园跑腿骑手端 4其他"),
     *   @OA\Response(
     *     response=200,
     *     description="响应",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionIndustry()
    {
        $model = $this->getModel(Yii::$app->request->get('scene'));
        $data = $model->getIndustry();
        return $this->success($data);
    }

    /**
     * @OA\Post(path="/wechat/tpl/addtmp",
     *   summary="添加公众号模板",
     *   tags={"微信消息模板"},
     *   @OA\Parameter(name="access-token",in="header",required=true,@OA\Schema(type="string"),description="登录凭证"),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(property="tmp_id",type="string",description="模板编号"),
     *         @OA\Property(property="scene",type="integer",description="1默认官方官网 2校园跑腿用户端 3校园跑腿骑手端 4其他"),
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="响应",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionAddtmp()
    {
        $tmp_id = Yii::$app->request->post('tmp_id');
        if (!$tmp_id) {
            return $this->fail('模板编号无效');
        }
        $model = $this->getModel(Yii::$app->request->post('scene'));
        $data = $model->addTemplate($tmp_id);
        return $this->success($data);
    }

    /**
     * @OA\Post(path="/wechat/tpl/deletetmp",
     *   summary="删除公众号模板",
     *   tags={"微信消息模板"},
     *   @OA\Parameter(name="access-token",in="header",required=true,@OA\Schema(type="string"),description="登录凭证"),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(property="tmp_id",type="string",description="模板编号"),
     *         @OA\Property(property="scene",type="integer",description="1默认官方官网 2校园跑腿用户端 3校园跑腿骑手端 4其他"),
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="响应",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionDeletetmp()
    {
        $tmp_id = Yii::$app->request->post('tmp_id');
        if (!$tmp_id) {
            return $this->fail('模板编号无效');
        }
        $model = $this->getModel(Yii::$app->request->post('scene'));
        $data = $model->delPrivateTemplate($tmp_id);
        return $this->success($data);
    }
    
    public function getModel($scene=Mini::SCENE_WX_DEFAULT)
    {
        $model = Yii::createObject([
            'class' => Wechat::className(),
            'owner_id' => $this->current_owner_id,
            'user_id' => $this->current_user_id,
            'scene' => $scene,
        ]);
        $config = $model->config();
        // print_r($config);exit();
        return new Template($config);
    }
}
