<?php

namespace bricksasp\user\controllers;

use Yii;
use WeMini\Crypt;
use bricksasp\base\Tools;
use bricksasp\models\Mini;
use bricksasp\models\pay\Wechat;
use bricksasp\base\FrontendController;
use bricksasp\models\UserInfo;
use bricksasp\rbac\models\User;
use bricksasp\models\form\Register;

class LoginController extends FrontendController
{
	public function noLoginAction()
	{
		return [
			'code2',
            'login-by-phone',
		];
	}

    /**
     * @OA\Post(path="/user/login/code2",
     *   summary="小程序code2Session 登录凭证校验",
     *   tags={"user模块"},
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="code", type="string", description="小程序用户登录凭证"),
     *         @OA\Property(property="scene", type="integer", description="应用场景1默认官方官网 2校园跑腿用户端 3校园跑腿骑手端 4其他",example=1),
     *         @OA\Property(property="uinfo", type="object", description="用户信息",
     *           @OA\Property(property="gender", type="integer", description="性别"),
     *           @OA\Property(property="avatarUrl", type="string", description="头像"),
     *           @OA\Property(property="nickname", type="string", description="昵称"),
     *         ),
     *       )
     *     )
     *   ),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *           @OA\Property(property="token", type="string", description="登录Token"),
     *           @OA\Property(property="is_new_user", type="integer", description="是否是新用户1是0否"),
     *         ),
     *     ),
     *   ),
     * )
     * 
     */
    public function actionCode2()
    {
        $scene = Yii::$app->request->post('scene',Mini::SCENE_WX_DEFAULT);
		$model = Crypt::instance($this->wxConfig($this->current_owner_id, $scene));
		$res = $model->session(Yii::$app->request->post('code')??Tools::breakOff('code必填'));

		$user = UserInfo::find()->where(['scene'=>$scene, 'platform'=>Mini::PLATFORM_WX, 'owner_id'=>$this->current_owner_id, 'openid'=>$res['openid']??Tools::breakOff('code无效')])->one();
        $is_new_user = 0;
		if (!$user) {
            $is_new_user=1;
            $reg = new Register(['scenario' => Register::TYPE_WX_MINI]);
            $reg->load($this->sysParams(['openid'=>$res['openid'], 'scene'=>$scene, 'type'=>Mini::TYPE_WX_MINI, 'platform'=>Mini::PLATFORM_WX]),'');
            $user = $reg->signup();
            if (!$user) {
                return $this->fail($reg->errors?$reg->errors:$user->errors);
            }
		}
        $user->saveData(Yii::$app->request->post('uinfo',[]));
        return $this->success(array_merge(User::generateApiToken($user->user_id, 1),['is_new_user'=>$is_new_user]),'登录成功');
    }

    /**
     * @OA\Post(path="/user/login/login-by-phone",
     *   summary="小程序手机号码一键登录",
     *   tags={"user模块"},
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="code", type="string", description="小程序用户登录凭证"),
     *         @OA\Property(property="iv", type="string", description="小程序iv"),
     *         @OA\Property(property="encryptedData", type="string", description="小程序encryptedData"),
     *         @OA\Property(property="scene", type="integer", description="应用场景1默认官方官网 2校园跑腿用户端 3校园跑腿骑手端 4其他",example=1),
     *         @OA\Property(property="uinfo", type="object", description="用户信息",
     *           @OA\Property(property="gender", type="integer", description="性别"),
     *           @OA\Property(property="avatarUrl", type="string", description="头像"),
     *           @OA\Property(property="nickname", type="string", description="昵称"),
     *         ),
     *       )
     *     )
     *   ),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *           @OA\Property(property="token", type="string", description="登录Token"),
     *         ),
     *     ),
     *   ),
     * )
     * 
     */
    public function actionLoginByPhone()
    {
        $params = Yii::$app->request->post();
        $scene = Yii::$app->request->get('scene',Mini::SCENE_WX_DEFAULT);
        $model = Crypt::instance($this->wxConfig($this->current_owner_id, $scene));
        $res = $model->userInfo($params['code']??Tools::breakOff('code必填'), $params['iv']??Tools::breakOff('iv必填'), $params['encryptedData']??Tools::breakOff('encryptedData必填'));

        $user = UserInfo::find()->where(['scene'=>$scene, 'platform'=>Mini::PLATFORM_WX, 'owner_id'=>$this->current_owner_id, 'openid'=>$res['openid']??Tools::breakOff('code无效')])->one();
        $is_new_user = 0;
        if (!$user) {
            $is_new_user=1;
            $reg = new Register(['scenario' => Register::TYPE_WX_MINI]);
            $reg->load($this->sysParams(['openid'=>$res['openid'], 'scene'=>$scene, 'type'=>Mini::TYPE_WX_MINI, 'platform'=>Mini::PLATFORM_WX, 'mobile'=> $res['purePhoneNumber']]),'');
            $user = $reg->signup();
            if (!$user) {
                return $this->fail($reg->errors);
            }
        }
        $user->saveData(Yii::$app->request->post('uinfo',[]));
        return $this->success(array_merge(User::generateApiToken($user->user_id, 1),['is_new_user'=>$is_new_user]),'登录成功');
    }

    public function wxConfig($owner_id, $scene=1)
    {
    	$model = new Wechat();
    	$model->owner_id = $owner_id;
    	$model->scene = $scene;
    	return $model->config();
    }
}
