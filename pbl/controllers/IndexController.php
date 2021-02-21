<?php

namespace bricksasp\pbl\controllers;

use Yii;
use bricksasp\models\Sms;
use bricksasp\base\Tools;
use bricksasp\rbac\models\User;
use bricksasp\models\redis\Token;
use bricksasp\models\SearchKeywords;
use bricksasp\rbac\models\form\Login;
use bricksasp\models\LogisticsCompany;
use bricksasp\models\IndustryCategory;
use bricksasp\models\Setting;
use bricksasp\models\File;
use bricksasp\models\Region;

class IndexController extends \bricksasp\base\FrontendController
{
    /**
     * 免登录访问
     * @return array
     */
    public function noLoginAction() {
        return [
            'sms-vcode',
            'fileupload',
            'fileuploads',
            'check-vcode',
            'get-industry-category',
            'hot-keywords',
            'get-logistics',
            'config',
            'file',
            'region',
        ];
    }

    public function checkLoginAction() {
        return [
            'fileupload',
            'fileuploads',
        ];
    }

    /**
     * @OA\Post(
     *   path="/pbl/index/fileupload",
     *   summary="文件上传",
     *   description="更新图片时，带上原图片地址 oldFile",
     *   tags={"pbl模块"},
     *   operationId="",
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(description="文件 小于2M",property="file",type="file"),
     *         @OA\Property(description="原有文件地址，更新文件时使用",property="oldFile",type="string"),
     *         required={"file"}
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="文件结构",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(description="文件访问地址",property="file_url",type="string"),
     *       ),
     *     ),
     *   ),
     * )
     * */
    public function actions()
    {
      return [
        'fileupload' => [
          'class' => \bricksasp\base\FileAction::className(),
        ],
        'fileuploads' => [
          'class' => \bricksasp\base\FileAction::className(),
      ]
      ];
    }

    /**
	 * @OA\Post(path="/pbl/index/sms-vcode",
	 *   summary="发送短信验证码",
	 *   tags={"pbl模块"},
	 *   @OA\RequestBody(
	 *     @OA\MediaType(
     *       mediaType="application/json",
	 *       @OA\Schema(
	 *         @OA\Property(property="mobile",type="integer",description="手机号码",example=18782908511),
     *         @OA\Property(property="type",type="integer",description="1通用验证码2登录验证码3注册验证码",example=1),
     *         required={"mobile", "type"},
	 *       )
	 *     )
	 *   ),
	 *   @OA\Response(
	 *     response=200,
	 *     description="sms",
	 *     @OA\MediaType(
	 *       mediaType="application/json",
	 *       @OA\Schema(ref="#/components/schemas/response"),
	 *     ),
	 *   ),
	 * )
	 *
	 */
    public function actionSmsVcode()
    {
    	$model = new Sms();
    	$code = rand(10000,99999) . '';
    	$res = $model->sendsms(Yii::$app->request->post('mobile'), $code, Yii::$app->request->post('type',Sms::TYPE_VCODE_PATTERN),$this->current_owner_id);
    	if ($res) {
    		return $this->success(Yii::t('messages',980006), YII_DEBUG ? $code:'SUCCESS');
    	}
    	return $this->fail($model->errors);
    }

    /**
     * @OA\Get(path="/pbl/index/config",
     *   summary="网站基础配置",
     *   tags={"pbl模块"},
     *   
     *   @OA\Response(
     *     response=200,
     *     description="sms",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionConfig()
    {
        return $this->success(Setting::getSetting($this->current_owner_id, 'WEB_'));
    }

    /**
     * 
     * @OA\Post(path="/pbl/index/check-vcode",
     *   summary="短信验证码验证",
     *   tags={"pbl模块"},
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="phone",type="string",description="手机号", example="18782908511",),
     *         @OA\Property(property="type",type="integer",description="1通用验证码2登录验证码3注册验证码",example=1),
     *         @OA\Property(property="vcode",type="string",description="验证码"),
     *         required={"phone", "vcode", "type"}
     *       )
     *     )
     *   ),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *           ref="#/components/schemas/response",
     *         ),
     *       )
     *     ),
     *   ),
     * )
     */
     public function actionCheckVcode()
     {
        $model = new Sms();
        if ($model->checkVcode(Yii::$app->request->post('phone'),Yii::$app->request->post('vcode'),Yii::$app->request->post('type',Sms::TYPE_VCODE_PATTERN),$this->current_user_id)) {
            return $this->success();
        }
        return $this->fail($model->errors);
     }



    /**
     * @OA\Get(path="/pbl/index/get-industry-category",
     *   summary="经济行业分类",
     *   tags={"pbl模块"},
     *   @OA\Parameter(description="id",name="id",in="query",@OA\Schema(type="string")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/pagination"),
     *     ),
     *   ),
     * )
     *
     */
     public function actionGetIndustryCategory(){

         $id = Yii::$app->request->get('id');
         if($id){
             $one = IndustryCategory::findOne(['id'=>$id]);
             $condition['parent_id'] = $one['industry_id'];
         }else{
             $condition['level_type'] = 0;
         }
         $data = IndustryCategory::find()->Where($condition)->asArray()->all()
         ;
         if($data){
             return $this->success($data);
         }
        return $this->fail('获取数据失败');
     }


    /**
     * @OA\Get(path="/pbl/index/hot-keywords",
     *   summary="热搜词汇",
     *   tags={"pbl模块"},
     *   @OA\Parameter(name="type",in="query",@OA\Schema(type="string"),description="1商品2文章"),
     *   @OA\Response(
     *     response=200,
     *     description="参数列表",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response")
     *     ),
     *   ),
     * )
     */
    public function actionHotKeywords()
    {
        $models = SearchKeywords::find()->select(['keywords'])->where(['type'=>Yii::$app->request->get('type',1),'owner_id'=>$this->current_owner_id])->limit(10)->all();
        return $this->success($models);
    }


    /**
     * @OA\Get(path="/pbl/index/get-logistics",
     *   summary="获取快递公司",
     *   tags={"pbl模块"},
     *   @OA\Response(
     *     response=200,
     *     description="参数列表",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response")
     *     ),
     *   ),
     * )
     */
    public function actionGetLogistics(){
        $logisticsCompany = Yii::$app->redis->get('logistics_company');
        if(!$logisticsCompany){
            $logisticsCompany = LogisticsCompany::find()->asArray()->all();
            if(!$logisticsCompany){
                return $this->fail('为获取到数据');
            }
            Yii::$app->redis->set('logistics_company',json_encode($logisticsCompany,JSON_UNESCAPED_UNICODE));
        }
        return $this->success(json_decode($logisticsCompany,true));
    }

    /**
     *
     * @return array
     */
    public function actionGetInfo(){
        $authHeader = $this->request->getHeaders()->get('X-Token');
        $info = Token::find($authHeader);
        if(!isset($info['user_id'])){
            return $this->fail('无效token');
        }
        return $this->success($info);
    }

    /**
     * Login
     * @OA\Post(path="/pbl/index/login",
     *   summary="前台用户统一登录接口",
     *   tags={"pbl模块"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(description="用户名",property="username",type="string",default="bricksasp"),
     *         @OA\Property(description="密码",property="password",type="string",default="111111")
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response")
     *     ),
     *   ),
     * )
     */
    public function actionLogin()
    {
        $model = new Login();
        if ($model->load(Yii::$app->request->post(), '') && $model->login()) {
            $token = User::generateApiToken(Yii::$app->getUser()->id, 1);
            return $token == false ? $this->fail(Yii::t('messages',50002), 50002) : $this->success(['token' => $token]);
        };
        return $this->fail($model->errors);
    }

    /**
     * Login
     * @OA\get(path="/pbl/index/file",
     *   summary="统一文件访问",
     *   tags={"pbl模块"},
     *   
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="string"),description="文件id"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response")
     *     ),
     *   ),
     * )
     */
    public function actionFile()
    {
        $id = Yii::$app->request->get('id');
        if ($id) {
            if ($model = File::findOne($id)) {
                return $this->redirect($model->file_url);
            }
        }
        return $this->redirect('/default.jpeg');
    }

    /**
     * 区域级联选择
     * @OA\Get(path="/pbl/index/region",
     *   summary="区域级联选择(省市区乡)",
     *   tags={"pbl模块"},
     *   
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="string"),description="区域id"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="列表结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(@OA\Property(property="data", type="array", description="地区id", @OA\Items(ref="#/components/schemas/region"))),
     *     ),
     *   ),
     * )
     *
     * @OA\Schema(
     *   schema="region",
     *   description="地区树结构",
     *   @OA\Property(property="id", type="integer", description="地区id"),
     *   @OA\Property(property="code", type="integer", description="编码"),
     *   @OA\Property( property="name", type="string", description="名称"),
     * )
     */
    public function actionRegion($id=0)
    {
        return $this->success(Region::find()->select(['id','name'])->where(['parent_id' => $id])->all());
    }
}
