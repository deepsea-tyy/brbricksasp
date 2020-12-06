<?php
namespace bricksasp\rbac\components;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\web\Response;
use bricksasp\base\BaseTrait;

class CaptchaAction extends \yii\captcha\CaptchaAction
{
    use BaseTrait;
	public $key = null;

    /**
     * Runs the action.
     */
    public function run()
    {
    	$queryKey = Yii::$app->request->getQueryParam('key');
        if ($this->key === null) {
        	if (empty($queryKey)) {
        		$this->key = $this->key ? $this->key : md5('__captcha/' . $this->controller->id . $this->getUniqueId() . microtime());
        	}else{
        		$this->key = $queryKey;
        	}
        }

        $code = $this->getVerifyCode(Yii::$app->request->getQueryParam(self::REFRESH_GET_VAR) !== null ? true :false);
        if (empty($queryKey)) {
	        return $this->success([
	            'url' => Url::to([$this->id],true),
	            'params' => ['key' => $this->key]
	        ]);
        }

        $this->setHttpHeaders();
        Yii::$app->response->format = Response::FORMAT_RAW;

        return $this->renderImage($code);
    }

    /**
     * Gets the verification code.
     * @param bool $generate whether the verification code should be regenerated.
     * @return string the verification code.
     */
    public function getVerifyCode($generate=false)
    {
        $cache = Yii::$app->getCache();
		$code = $cache->get($this->key);
        if ($generate || $code === false) {
            if ($this->fixedVerifyCode !== null) {
                $code = $this->fixedVerifyCode;
            }else{
                $code = $this->generateVerifyCode();
            }
        	$cache->set($this->key, $code, 180);
        }
        return $code;
    }
}
