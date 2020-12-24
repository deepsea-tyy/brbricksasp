<?php
namespace bricksasp\models\pay;

use Yii;
use WeChat\Pay;
use yii\helpers\Url;
use yii\base\BaseObject;
use bricksasp\base\Tools;
use bricksasp\models\Mini;
use bricksasp\models\PaySetting;
use bricksasp\models\UserInfo;

/**
 * $scene 应用场景
 */
class Wechat extends BaseObject implements PayInterface
{
    public $money;
    public $owner_id;
    public $user_id;
    public $pay_id;
    public $ip;
    public $scene;
    public $body;

    public function config()
    {
        if (empty($this->scene)) {
            $this->scene = Mini::SCENE_WX_DEFAULT;
        }

        $app = Mini::find()->where(['owner_id'=>$this->owner_id, 'platform'=>Mini::PLATFORM_WX, 'scene'=>$this->scene])->one();
        $paySet = PaySetting::find()->where(['owner_id'=> $this->owner_id, 'platform'=>Mini::PLATFORM_WX])->one();
        if (!$app || !$paySet || ($paySet && !$paySet->config)) {
            Tools::breakOff(950002);
        }
        $payConf = json_decode($paySet->config, true);

        $paySet = json_decode($paySet->config,true);

        return [
            'appid'          => $app->appid,
            'appsecret'      => $app->app_secret,
            'encodingaeskey' => $app->encoding_key,
            'mch_id'         => $payConf['mch_id'],
            'mch_key'        => $payConf['mch_key'],
            // 配置商户支付双向证书目录（可选，在使用退款|打款|红包时需要）
            'ssl_key'        => $payConf['ssl_key'],
            'ssl_cer'        => $payConf['ssl_cer'],
            'notify_url'     => $payConf['notify_url'],
            'redirect_url'     => $payConf['redirect_url'],
            // 缓存目录配置（可选，需拥有读写权限）
            'cache_path'     => Yii::getAlias('@runtime') . '/cache/wx',
        ];
    }
    
    // 扫码支付
    public function qr(){
        $cfg = $this->config();
        $payModel = new Pay($cfg);
        $result = $payModel->createOrder($this->getOption($cfg['notify_url'],'NATIVE'));
        $result['code_url'] = Yii::$app->request->hostInfo . Url::toRoute(['/qr/img', 'content' => $result['code_url']]);
        return $result;
    }

    public function lite(){
        $cfg = $this->config();

        $user = UserInfo::find()->select(['open_id'])->where(['user_id'=>$this->user_id])->one();
        $payModel = new Pay($cfg);

        try {
            // 生成预支付码
            $params = $this->getOption($cfg['notify_url']);
            $params['openid'] = $user->open_id;
            
            $result = $payModel->createOrder($params);
            
            // 创建JSAPI参数签名
            return $payModel->createParamsForJsApi($result['prepay_id']);
        } catch (Exception $e) {
            Tools::breakOff($e->getMessage());
        }
    }

    // 组装参数，可以参考官方商户文档
    public function getOption($notify_url, $trade_type='JSAPI')
    {
        $map = [
            'user_id'=> $this->user_id,
            'owner_id'=> $this->owner_id,
            'scene'=> $this->scene,
        ];
        return [
            'body'             => $this->body ?? '新支付单:'.$this->pay_id. '收款 ' . $this->money,
            'out_trade_no'     => $this->pay_id,
            // 'total_fee'        => $this->money * 100,
            'total_fee'        => 1,
            'trade_type'       => $trade_type,
            'notify_url'       => $notify_url,
            'spbill_create_ip' => $this->ip,
            'attach'           => base64_encode(json_encode($map))
        ];
    }

    public function app(){}
    public function bar(){}
    public function wap(){}
    public function pub(){}
    public function refund(){}
    public function query(){}
}