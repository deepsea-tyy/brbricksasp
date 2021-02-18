<?php

namespace bricksasp\base;

use Yii;
use yii\filters\Cors;
use bricksasp\models\redis\Token;
use bricksasp\models\AddonInstall;

class BaseController extends \yii\web\Controller
{
	use BaseTrait;
    public $enableCsrfValidation = false;
	public $current_user_id = null; //当前用户id
	public $current_owner_id = null; //数据所属商户id
	public $current_login_type = null; //登录类型
	public $current_accont_type = null; //账号类型

	public function init() {
		parent::init();
		
		$paginationSettings = [
		    'pageSizeParam' => 'pageSize',
		    'pageSize' => (int)Yii::$app->request->get('pageSize',10),
		];
		Yii::$container->set('yii\data\Pagination', $paginationSettings);
	}
/*
   public function behaviors()
   {
       return [
           'corsFilter'=>[
               'class' => Cors::className(),
               'cors'=>[
                   'Access-Control-Allow-Origin' => ['*'],
                   'Access-Control-Allow-Method' => ['*'],
                   'Access-Control-Allow-Headers' => ['*'],
                   'Origin' => ['*'],
               ]
           ]
       ];
   }*/

	/**
	 * 免登录访问
	 * @return array
	 */
	public function noLoginAction() {
		return [
			'error',
		];
	}

	/**
	 * 免登录访问token存在时获取user_id
	 * @return array
	 */
	public function checkLoginAction() {
		return [];
	}

	/**
	 * 登录访问 其他需授权
	 * @return array
	 */
	public function loginAction() {
		return [];
	}

    public function actionError()
    {
        $msg = Yii::$app->getErrorHandler()->exception->getMessage();
        if (is_numeric($msg)) return $this->fail(Yii::t('messages',$msg), (int)$msg);
        return $this->fail($msg);
    }

	/**
	 * 组装查询条件
	 * @return array
	 */
	protected function queryMapGet()
	{
		return $this->sysParams(Yii::$app->request->get());
	}

	protected function queryMapPost()
	{
		return $this->sysParams(Yii::$app->request->post());
	}

	/**
	 * 系统参数
	 * @return array 
	 */
	protected function sysParams($params=[])
	{
		return array_merge($params, [
			'current_user_id' => $this->current_user_id,
			'current_owner_id' => $this->current_owner_id,
			'current_login_type' => $this->current_login_type,
		]);
	}

	/**
	 * 数据所属条件
	 * @return array
	 */
	protected function ownerCondition()
	{
		if (!($this->current_login_type == Token::TOKEN_TYPE_BACKEND && $this->current_user_id == 1)) {
			$cdt['owner_id'] = $this->current_owner_id;
		}

		if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND) {
			$cdt['user_id'] = $this->current_user_id;
		}
		return $cdt??[];
	}

	/**
	 * 数据修改条件
	 * @return array
	 */
	protected function updateCondition(array $cdt)
	{
		if (!($this->current_login_type == Token::TOKEN_TYPE_BACKEND && $this->current_user_id == 1)) {
			$cdt['owner_id'] = $this->current_owner_id;
		}

		if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND) {
			$cdt['user_id'] = $this->current_user_id;
		}
		return $cdt;
	}
	
}
