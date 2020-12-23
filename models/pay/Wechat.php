<?php
namespace bricksasp\models\pay;

use Yii;
use WeChat\Pay;
use yii\helpers\Url;
use yii\base\BaseObject;
use bricksasp\base\Tools;
use bricksasp\models\Mini;
use bricksasp\models\PaySetting;

class Wechat extends BaseObject implements PayInterface
{
    public $money;
    public $owner_id;
    public $user_id;
    public $pay_id;
    public $ip;
    public $data;
    public $scene;

    public static function config($owner_id, $type=self::WX_TYPE_OFFICIAL)
    {
        if (empty($this->scene)) {
            $this->scene = PaySetting::SCENE_DEFAULT;
        }
        $app = Mini::find()->where(['owner_id'=>$this->owner_id, 'platform'=>Mini::PLATFORM_WX, 'type'=>Mini::SCENE_WX_DEFAULT])->one();

        $paySet = PaySetting::find()->where(['owner_id'=> $this->owner_id, 'platform'=>Mini::PLATFORM_WX])->one();
        if (!$app || !$paySet) {
            Tools::breakOff(950002);
        }

        $paySet = json_decode($paySet->config,true);

        return [
            'appid'          => $app[''],
            'appsecret'      => $appsecret,
            'encodingaeskey' => '',
            'mch_id'         => $paySet['mch_id'],
            'mch_key'        => $paySet['md5_key'],
            // 配置商户支付双向证书目录（可选，在使用退款|打款|红包时需要）
            'ssl_key'        => $paySet['app_key_pem'],
            'ssl_cer'        => $paySet['app_cert_pem'],
            // 缓存目录配置（可选，需拥有读写权限）
            'cache_path'     => Yii::getAlias('@runtime') . '/cache/' . $owner_id,
            'notify_url'     => $paySet['notify_url'],
            'redirect_url'     => $paySet['redirect_url'],
        ];
    }

    public function app(){
        return [];
    }

    public function bar();
    
    // 扫码支付
    public function qr(){

        $cfg = self::config($this->data['owner_id'], self::WX_TYPE_OFFICIAL);
        
        $payModel = new Pay($cfg);
        $result = $payModel->createOrder($this->getOption($cfg['notify_url'],'NATIVE'));
        $result['code_url'] = Yii::$app->params['globalParams']['webUrl'] . Url::toRoute(['/qr/img', 'content' => $result['code_url']]);
        return $result;
    }
    // public function wap();
    // public function pub(){};

    public function lite(){
        $cfg = self::config($this->data['owner_id'], self::WX_TYPE_APPLET);
        $openid = UserConnect::find()->where(['user_id'=>$this->data['user_id'],'type'=>UserWx::REGISTER_TYPE_XCX])->one();
        $payModel = new Pay($cfg);

        try {
            // 生成预支付码
            $params = $this->getOption($cfg['notify_url']);
            $params['openid'] = $openid->openid;
            
            $result = $payModel->createOrder($params);
            
            // 创建JSAPI参数签名
            return  $payModel->createParamsForJsApi($result['prepay_id']);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }

    // 组装参数，可以参考官方商户文档
    public function getOption($notify_url, $trade_type='JSAPI')
    {
        $map = [
            'user_id'=> $this->data['user_id'],
            'owner_id'=> $this->data['owner_id']
        ];
        return [
            'body'             => $this->getBody(),
            'out_trade_no'     => $this->data['payment_id'],
            // 'total_fee'        => $this->data['money'] * 100,
            'total_fee'        => 1,
            'trade_type'       => $trade_type,
            'notify_url'       => $notify_url,
            'spbill_create_ip' => $this->data['ip'],
            'attach'           => base64_encode(json_encode($map))
        ];
    }

    public function pay(){
        return call_user_func_array([$this,$this->type],[]);
    }

    public function refund(){
    }

    public function query(){}

    public function getBody(){
        $item = array_pop($this->data['orderItems']);
        return (mb_substr($item['name'],0,28,'utf8')??'订单号') . $this->data['order_id'];
    }
}