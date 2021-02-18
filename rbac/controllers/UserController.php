<?php
namespace bricksasp\rbac\controllers;

use Yii;
use yii\helpers\Url;
use yii\mail\BaseMailer;
use yii\filters\VerbFilter;
use bricksasp\rbac\components\Configs;
use bricksasp\rbac\models\form\Login;
use bricksasp\rbac\models\form\Signup;
use bricksasp\rbac\models\User;
use bricksasp\models\redis\Token;
use bricksasp\base\BackendController;
use bricksasp\base\Tools;
use Endroid\QrCode\QrCode;
use GatewayClient\Gateway;

/**
 * User controller
 */
class UserController extends BackendController {
	/**
	 * 登录可访问 其他需授权
	 * @return array
	 */
	public function loginAction() {
		return [
			'info',
			'index',
			'logout',
		];
	}

	/**
	 * 免登录可访问
	 * @return array
	 */
	public function noLoginAction() {
		return [
			'login',
			'signup',
			'captcha',
			'qrlogin',
			'qrscan',
		];
	}

    /**
     * 
     * @OA\Get(path="/user/captcha",
     *   summary="验证码  刷新验证码 例 {{url}}?key=111&refresh",
     *   tags={"管理后台全局接口"},
     *   @OA\Response(
     *     response=200,
     *     description="验证码",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *         @OA\Property(description="验证码图片url",property="url",type="string"),
     *         @OA\Property(property="params", type="array", description="验证码参数", @OA\Items(
     *              @OA\Property(property="key", type="integer", description="验证码key"),
     *           ),
     *         )
     *       )
     *     ),
     *   ),
     * )
     */
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'bricksasp\rbac\components\CaptchaAction',
                'height' => 50,
                'width' => 80,
                'minLength' => 4,
                'maxLength' => 4,

                //测试固定验证码
                'key' => '65e83d23146f1ee056ef2aa622b179dc',
                'fixedVerifyCode' => 1234,
            ],
        ];
    }

	/**
	 * 用户信息
	 * @return array
	 */
	public function actionInfo() {
		$info = [
			'id' => $this->current_user_id,
			'roles' => array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId())),
            'avatar' => 'http://www.bricksasp.com/static/img/beian2.gif'
		];
		return $this->success($info);
	}

	/**
	 * Login
     * @OA\Post(path="/user/login",
     *   summary="管理员登录",
     *   tags={"管理后台全局接口"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(description="用户名",property="username",type="string",default="bricksasp"),
     *         @OA\Property(description="密码",property="password",type="string",default="111111"),
     *         @OA\Property(description="验证码 访问地址查看验证码{{url}}/user/captcha?key=65e83d23146f1ee056ef2aa622b179dc",property="code",type="string",default="1234"),
     *         @OA\Property(description="验证码key",property="key",type="string",default="65e83d23146f1ee056ef2aa622b179dc")
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="登录信息",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response")
     *     ),
     *   ),
     * )
	 */
	public function actionLogin() {
		$model = new Login();
		if ($model->load(Yii::$app->request->post(), '') && $model->login()) {
            $auth = Configs::authManager();
            if (!$auth->getAssignment('IsAdmin',Yii::$app->getUser()->id)) {
                Tools::breakOff(50007);
            }
			$token = User::generateApiToken(Yii::$app->getUser()->id, 2);
			if ($token) {
				return $this->success($token);
			}
			Tools::breakOff(50002);
		}
		return $this->fail($model->errors);
	}

	/**
	 * Logout
     * @OA\Post(path="/user/logout",
     *   summary="管理员退出登录",
     *   tags={"管理后台全局接口"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   @OA\Response(
     *     description="响应结构",
     *     response=200,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
	 */
	public function actionLogout() {
    	User::destroyApiToken(Yii::$app->request->getHeaders()->get('access-token'));
		Tools::breakOff(50001);
	}

	/**
	 * Signup new user
     * @OA\Post(path="/user/signup",
     *   summary="用户注册",
     *   tags={"管理后台全局接口"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(description="用户名",property="username",type="string"),
     *         @OA\Property(description="邮箱",property="email",type="string"),
     *         @OA\Property(description="密码",property="password",type="string"),
     *         @OA\Property(description="确认密码",property="retypePassword",type="string"),
     *         @OA\Property(description="手机号",property="mobile",type="integer"),
     *         @OA\Property(description="验证码 访问地址查看验证码{{url}}/user/captcha?key=65e83d23146f1ee056ef2aa622b179dc",property="code",type="string",default="1234"),
     *         @OA\Property(description="验证码key.",property="key",type="string",default="65e83d23146f1ee056ef2aa622b179dc"),
     *         @OA\Property(description="是否返回登录token 1是 2否",property="lognin",type="integer",default=2,),
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
	 */
	public function actionSignup() {
		$model = new Signup();

		if ($model->load($this->queryMapPost(), '')) {
			if ($user = $model->signup()) {
				if (Yii::$app->request->post('lognin') == 1) {
					return $this->success($user->generateApiToken($user->id));
				}
				return $this->success();
			}
		}

		return $this->fail($model->errors);
	}
}
