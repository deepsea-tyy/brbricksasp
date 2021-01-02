<?php
namespace bricksasp\models\platform;

use Yii;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Sms\V20190711\SmsClient;
use TencentCloud\Sms\V20190711\Models\SendSmsRequest;
use bricksasp\base\Tools;

/**
 * 腾讯云短信
 */
class Tencent
{
	public $SecretId;
	public $SecretKey;
	public $Region = 'ap-chengdu';
	public $TemplateID;
	public $SmsSdkAppid;
	public $Sign;
	
	public function send(array $PhoneNumberSet,array $TemplateParamSet)
	{
		try {
		    $cred = new Credential($this->SecretId, $this->SecretKey);
		    $httpProfile = new HttpProfile();
		    $httpProfile->setEndpoint("sms.tencentcloudapi.com");
		      
		    $clientProfile = new ClientProfile();
		    $clientProfile->setHttpProfile($httpProfile);
		    $client = new SmsClient($cred, $this->Region, $clientProfile);

		    $req = new SendSmsRequest();
		    
		    $params = array(
		        "PhoneNumberSet" => array_map(function ($item)
		        {
		        	return '86' . $item;
		        }, $PhoneNumberSet),
		        "TemplateID" => $this->TemplateID,
		        "Sign" => $this->Sign,
		        "TemplateParamSet" => $TemplateParamSet,
		        "SmsSdkAppid" => $this->SmsSdkAppid
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
		    Yii::error(array_merge($params,['error'=>'curl请求失败']),'sms');
		}
		catch(TencentCloudSDKException $e) {
        	Tools::breakOff($e->getMessage());
		}
        Tools::breakOff('920005');
	}
}