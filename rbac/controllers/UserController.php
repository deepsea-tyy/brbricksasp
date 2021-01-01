<?php
namespace bricksasp\rbac\controllers;

use Yii;
use yii\helpers\Url;
use yii\mail\BaseMailer;
use yii\base\UserException;
use yii\filters\VerbFilter;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use bricksasp\rbac\components\Configs;
use bricksasp\rbac\components\UserStatus;
use bricksasp\rbac\models\form\ChangePassword;
use bricksasp\rbac\models\form\Login;
use bricksasp\rbac\models\form\PasswordResetRequest;
use bricksasp\rbac\models\form\ResetPassword;
use bricksasp\rbac\models\form\Signup;
use bricksasp\rbac\models\searchs\User as UserSearch;
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
	private $_oldMailPath;

	/**
	 * @inheritdoc
	 */
	public function behaviors() {
		return array_merge(parent::behaviors(), [
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'delete' => ['post'],
					'logout' => ['post'],
					'activate' => ['post'],
				],
			],
		]);
	}

	/**
	 * 登录可访问 其他需授权
	 * @return array
	 */
	public function loginAction() {
		return [
			'info',
			'index',
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
	 * @inheritdoc
	 */
	public function beforeAction($action) {
		if (parent::beforeAction($action)) {
			if (Yii::$app->has('mailer') && ($mailer = Yii::$app->getMailer()) instanceof BaseMailer) {
				/* @var $mailer BaseMailer */
				$this->_oldMailPath = $mailer->getViewPath();
				$mailer->setViewPath('@bricksasp/rbac/mail');
			}
			return true;
		}
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function afterAction($action, $result) {
		if ($this->_oldMailPath !== null) {
			Yii::$app->getMailer()->setViewPath($this->_oldMailPath);
		}
		return parent::afterAction($action, $result);
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
        $tokenHeader = Yii::$app->request->getHeaders()->get('auth-token');
        if ($tokenHeader) {
        	User::destroyApiToken($tokenHeader);
			Yii::$app->getUser()->logout();
			return $this->success();
        }
		Tools::breakOff(40005);
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

		if ($model->load($this->queryMap(Yii::$app->getRequest()->post()), '')) {
			if ($user = $model->signup()) {
				if (Yii::$app->getRequest()->post('lognin') == 1) {
					return $this->success($user->generateApiToken($user->id));
				}
				return $this->success();
			}
		}

		return $this->fail($model->errors);
	}

	/**
	 * Request reset password
	 * @return string
	 */
	public function actionRequestPasswordReset() {
		$model = new PasswordResetRequest();
		if ($model->load(Yii::$app->getRequest()->post()) && $model->validate()) {
			if ($model->sendEmail()) {
				Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');

				return $this->goHome();
			} else {
				Yii::$app->getSession()->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
			}
		}

		return $this->render('requestPasswordResetToken', [
			'model' => $model,
		]);
	}

	/**
	 * Reset password
	 * @return string
	 */
	public function actionResetPassword($token) {
		try {
			$model = new ResetPassword($token);
		} catch (InvalidParamException $e) {
			throw new BadRequestHttpException($e->getMessage());
		}

		if ($model->load(Yii::$app->getRequest()->post()) && $model->validate() && $model->resetPassword()) {
			Yii::$app->getSession()->setFlash('success', 'New password was saved.');

			return $this->goHome();
		}

		return $this->render('resetPassword', [
			'model' => $model,
		]);
	}

	/**
	 * Reset password
	 * @return string
	 */
	public function actionChangePassword() {
		$model = new ChangePassword();
		if ($model->load(Yii::$app->getRequest()->post()) && $model->change()) {
			return $this->goHome();
		}

		return $this->render('change-password', [
			'model' => $model,
		]);
	}

	/**
	 * Activate new user
	 * @param integer $id
	 * @return type
	 * @throws UserException
	 * @throws NotFoundHttpException
	 */
	public function actionActivate($id) {
		/* @var $user User */
		$user = $this->findModel($id);
		if ($user->status == UserStatus::INACTIVE) {
			$user->status = UserStatus::ACTIVE;
			if ($user->save()) {
				return $this->goHome();
			} else {
				$errors = $user->firstErrors;
				throw new UserException(reset($errors));
			}
		}
		return $this->goHome();
	}

	/**
	 * Finds the User model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return User the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id) {
		if (($model = User::findOne($id)) !== null) {
			return $model;
		} else {
			Tools::breakOff(40001);
		}
	}
}
