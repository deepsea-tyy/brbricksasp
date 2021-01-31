<?php
namespace bricksasp\base;

use bricksasp\base\Tools;
use bricksasp\models\redis\Token;
use bricksasp\rbac\components\Helper;

class CompositeAuth extends \yii\filters\auth\AuthMethod {
	public $tokenParam = 'auth-token';
	public $tokenHeader = 'access-token';

	public function authenticate($user, $request, $response) {
		$accessToken = $request->get($this->tokenParam);
		$authHeader = $request->getHeaders()->get($this->tokenHeader);

		if ($accessToken) {
			$identity = $user->loginByAccessToken($accessToken, Token::TOKEN_TYPE_ACCESS);
			if (empty($identity->shop_id)) {
				Tools::breakOff(50003);
			}
			$this->owner->current_owner_id = $identity->shop_id;
		}else{
			$this->owner->current_owner_id = 1;
		}

		$action = $this->owner->action;
		// 免登录访问
		if (in_array($action->id, $this->owner->noLoginAction())) {
			if ($authHeader && in_array($action->id, $this->owner->checkLoginAction())) {
				$currentUser = $user->loginByAccessToken($authHeader);
				if ($currentUser) {
					$this->owner->current_user_id = $currentUser->id;
					$this->owner->current_accont_type = $currentUser->type;
					if ($currentUser->token_type == Token::TOKEN_TYPE_BACKEND) {
						// 1全部数据 2商户数据 3前台展示数据
						$this->owner->current_login_type = Token::TOKEN_TYPE_BACKEND;
					}
				}
			}
			if (empty($this->owner->current_login_type)) {
				$this->owner->current_login_type = Token::TOKEN_TYPE_FRONTEND;
			}
			return true;
		}

		// 登录访问
		if ($authHeader) {
			$currentUser = $user->loginByAccessToken($authHeader);

			if ($currentUser === null) {
				Tools::breakOff(50001);
			}
			
			$this->owner->current_user_id = $currentUser->id;
			$this->owner->current_accont_type = $currentUser->type;

			if ($currentUser->token_type == Token::TOKEN_TYPE_FRONTEND) {
				//登录访问
				$this->owner->current_login_type = Token::TOKEN_TYPE_FRONTEND;
				if ($this->owner->hasMethod('loginAction') && in_array($action->id, $this->owner->loginAction())) {
					return true;
				}
			}
			
			if ($currentUser->token_type == Token::TOKEN_TYPE_BACKEND) {
				//授权访问
				$this->owner->current_login_type = Token::TOKEN_TYPE_BACKEND;
				if (Helper::checkRoute('/' . $action->getUniqueId(), $request->get(), $user->setIdentity($currentUser))) {
					return true;
				}
				Tools::breakOff(50004);
			}
			Tools::breakOff(50005);
		}
		Tools::breakOff(50001);
	}
}
