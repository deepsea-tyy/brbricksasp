<?php
namespace bricksasp\models\platform;

use Yii;
use bricksasp\base\Tools;
use TencentCloud\Common\Credential;
use TencentCloud\Sms\V20190711\SmsClient;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Sms\V20190711\Models\SendSmsRequest;
use TencentCloud\Common\Exception\TencentCloudSDKException;

/**
 * 腾讯云短信
 */
class Tencent
{
	public $sid;
	public $skey;
	public $Region = 'ap-chengdu';
	public $tpl_id;
	public $appid;
	public $sign;
	
	public function send(array $phones,array $tpl_params)
	{
		try {
		    $cred = new Credential($this->sid, $this->skey);
		    $httpProfile = new HttpProfile();
		    $httpProfile->setEndpoint('sms.tencentcloudapi.com');
		      
		    $clientProfile = new ClientProfile();
		    $clientProfile->setHttpProfile($httpProfile);
		    $client = new SmsClient($cred, $this->Region, $clientProfile);

		    $req = new SendSmsRequest();
		    
		    $params = array(
		        'PhoneNumberSet' => array_map(function ($item)
		        {
		        	return '86' . $item;
		        }, $phones),
		        'TemplateID' => $this->tpl_id,
		        'Sign' => $this->sign,
		        'TemplateParamSet' => $tpl_params,
		        'SmsSdkAppid' => $this->appid
		    );
		    $req->fromJsonString(json_encode($params));

		    $resp = $client->SendSms($req);
		    // print_r($resp);exit;
		    if ($resp->SendStatusSet) {
		    	foreach ($resp->SendStatusSet as $item) {
		    		if ($item->Code != 'OK') {
	    				Yii::error($item, 'sms');
		    		}
		    	}
		    	return true;
		    }
		    Tools::breakOff('请重试');
		}
		catch(TencentCloudSDKException $e) {
        	Tools::breakOff($e->getMessage());
		}
        Tools::breakOff('920005');
	}
}