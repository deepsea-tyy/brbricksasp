<?php

namespace bricksasp\wechat\controllers;

use Yii;
use WeMini\Newtmpl;
use yii\httpclient\Client;
use yii\base\DynamicModel;
use bricksasp\models\pay\Wechat;
use bricksasp\models\Mini;

/**
 * 小程序订阅消息
 */
class MiniTplController extends \bricksasp\base\BackendController
{
    /**
     * @OA\Get(path="/wechat/mini-tpl/index",
     *   summary="小程序模板列表",
     *   tags={"微信消息模板"},
     *   @OA\Parameter(name="access-token",in="header",required=true,@OA\Schema(type="string"),description="登录凭证"),
     *   @OA\Parameter(name="scene",in="query",@OA\Schema(type="integer"),description="1默认官方官网 2校园跑腿用户端 3校园跑腿骑手端 4其他"),
     *   @OA\Response(
     *     response=200,
     *     description="响应",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     * 
     *
     */
    public function actionIndex()
    {
        // $a = MessageTemplate::syssend(10321,'TEMP_ORDER_PAID',[
        //     ['value' => '16055922642064'],
        //     ['value' => '2020年11月30日 18:36'],
        //     ['value' => '8.00'],
        //     ['value' => '微信支付'],
        // ]);
        // return $this->success($a);
        $model = $this->getModel(Yii::$app->request->get('scene'));
        $data = $model->getTemplateList();

        return $this->success($data['data']);
    }

    /**
     * @OA\Get(path="/wechat/mini-tpl/category",
     *   summary="获取小程序账号的类目",
     *   tags={"微信消息模板"},
     *   @OA\Parameter(name="access-token",in="header",required=true,@OA\Schema(type="string"),description="登录凭证"),
     *   @OA\Parameter(name="scene",in="query",@OA\Schema(type="integer"),description="1默认官方官网 2校园跑腿用户端 3校园跑腿骑手端 4其他"),
     *   @OA\Response(
     *     response=200,
     *     description="响应",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionCategory()
    {
        $model = $this->getModel(Yii::$app->request->get('scene'));
        $data = $model->getCategory();
        return $this->success($data['data']);
    }

    /**
     * @OA\Get(path="/wechat/mini-tpl/pubtmp",
     *   summary="小程序公共模板列表",
     *   tags={"微信消息模板"},
     *   @OA\Parameter(name="access-token",in="header",required=true,@OA\Schema(type="string"),description="登录凭证"),
     *   @OA\Parameter(name="start",in="query",@OA\Schema(type="string"),description="用于分页，表示从 start 开始。从 0 开始计数"),
     *   @OA\Parameter(name="limit",in="query",@OA\Schema(type="string"),description="用于分页，表示拉取 limit 条记录。最大为 30。"),
     *   @OA\Parameter(name="keyword",in="query",@OA\Schema(type="string"),description="关键字 取消订单"),
     *   @OA\Parameter(name="scene",in="query",@OA\Schema(type="integer"),description="1默认官方官网 2校园跑腿用户端 3校园跑腿骑手端 4其他"),
     *   @OA\Response(
     *     response=200,
     *     description="响应",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionPubtmp()
    {
        $start = Yii::$app->request->get('start');
        $limit = Yii::$app->request->get('limit');
        $keyword = Yii::$app->request->get('keyword','');

        $model = $this->getModel(Yii::$app->request->get('scene'));
        $cat = $model->getCategory();
        $ids = implode(',', array_column($cat['data'], 'id'));

        $data = $model->getPubTemplateTitleList(['ids' => $ids, 'start' => $start?(string)$start:'0', 'limit' => $limit?(string)$limit:'30', 'keyword'=>$keyword]);
        return $this->success($data['data']);
    }

    /**
     * @OA\Post(path="/wechat/mini-tpl/tmpkw",
     *   summary="小程序模板标题下的关键词列表",
     *   tags={"微信消息模板"},
     *   @OA\Parameter(name="access-token",in="header",required=true,@OA\Schema(type="string"),description="登录凭证"),
     *   
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(property="tmp_id",type="string",description="模板标题 id"),
     *         @OA\Property(property="scene",type="integer",description="1默认官方官网 2校园跑腿用户端 3校园跑腿骑手端 4其他"),
     *       )
     *     )
     *   ),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionTmpkw()
    {
        $tmp_id = Yii::$app->request->post('tmp_id');
        if (!$tmp_id) {
            return $this->fail('模板编号无效');
        }
        $model = $this->getModel(Yii::$app->request->post('scene'));
        $data = $model->getPubTemplateKeyWordsById($tmp_id);
        return $this->success($data['data']);
    }

    /**
     * @OA\Post(path="/wechat/mini-tpl/addtmp",
     *   summary="小程序添加模板",
     *   tags={"微信消息模板"},
     *   @OA\Parameter(name="access-token",in="header",required=true,@OA\Schema(type="string"),description="登录凭证"),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(property="tmp_id",type="string",description="模板编号"),
     *         @OA\Property(property="kidList",type="array",description="开发者自行组合好的模板关键词列表，关键词顺序可以自由搭配（例如 3,5,4 或 4,5,3），最多支持5个，最少2个关键词组合",items={}),
     *         @OA\Property(property="sceneDesc",type="string",description="服务场景描述，15个字以内"),
     *         @OA\Property(property="scene",type="integer",description="1默认官方官网 2校园跑腿用户端 3校园跑腿骑手端 4其他"),
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="响应",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionAddtmp()
    {
        $rule = [
            [['tmp_id', 'kidList', 'sceneDesc'], 'required']
        ];
        $valid = DynamicModel::validateData(array_merge(['tmp_id'=>null,'kidList'=>null,'sceneDesc'=>null], Yii::$app->request->post()),$rule);
        if (!$valid->hasErrors()) {
            $model = $this->getModel(Yii::$app->request->post('scene'));
            $data = $model->addTemplate($valid->tmp_id,explode(',', $valid->kidList),$valid->sceneDesc);
            return $this->success($data);
        }
        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/wechat/mini-tpl/deletetmp",
     *   summary="小程序删除模板",
     *   tags={"微信消息模板"},
     *   @OA\Parameter(name="access-token",in="header",required=true,@OA\Schema(type="string"),description="登录凭证"),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(property="tmp_id",type="string",description="模板编号"),
     *         @OA\Property(property="scene",type="integer",description="1默认官方官网 2校园跑腿用户端 3校园跑腿骑手端 4其他"),
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="响应",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionDeletetmp()
    {
        $tmp_id = Yii::$app->request->post('tmp_id');
        if (!$tmp_id) {
            return $this->fail('模板编号无效');
        }
        $model = $this->getModel(Yii::$app->request->post('scene'));
        $data = $model->delTemplate($tmp_id);
        return $this->success($data);
    }

    public function getModel($scene=Mini::SCENE_WX_DEFAULT)
    {
        $model = Yii::createObject([
            'class' => Wechat::className(),
            'owner_id' => $this->current_owner_id,
            'user_id' => $this->current_user_id,
            'scene' => $scene,
        ]);
        $config = $model->config();
        return new Newtmpl($config);
    }
}
