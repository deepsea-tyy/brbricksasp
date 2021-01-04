<?php

namespace bricksasp\backend\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\Mini;
use bricksasp\models\SysTplMsg;
use yii\data\ActiveDataProvider;
use bricksasp\models\pay\Wechat;
use bricksasp\base\BackendController;
use WeMini\Newtmpl;
use WeChat\Template;

/**
 * SysTplMsgController implements the CRUD actions for SysTplMsg model.
 */
class SysTplMsgController extends BackendController
{
    /**
     * @OA\Get(path="/backend/sys-tpl-msg/index",
     *   summary="系统模板消息列表",
     *   tags={"backend模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
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
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->get();
        $query = SysTplMsg::find();
        $query->andFilterWhere($this->ownerCondition());

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $this->success([
          'list' => $dataProvider->models,
          'pageCount' => $dataProvider->pagination->pageCount,
          'totalCount' => $dataProvider->pagination->totalCount,
          'page' => $dataProvider->pagination->page + 1,
          'pageSize' => $dataProvider->pagination->limit,
        ]);
    }

    /**
     * @OA\Post(path="/backend/sys-tpl-msg/setting",
     *   summary="设置系统模板消息",
     *   tags={"backend模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/SysTplMsgCreate"
     *       )
     *     )
     *   ),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     *
     * @OA\Schema(
     *   schema="SysTplMsgCreate",
     *   description="系统模板消息",
     *   @OA\Property(property="code", type="string", description="模版标识"),
     *   @OA\Property(property="wx_tpl_id", type="string", description="公众号模板id", example=""),
     *   @OA\Property(property="wx_content", type="string", description="公众号模板内容"),
     *   @OA\Property(property="wx_mini_tpl_id", type="string", description="小程序模板id", example=""),
     *   @OA\Property(property="wx_mini_content", type="string", description="小程序模板内容"),
     *   @OA\Property(property="scene", type="integer", description="1默认官方官网 2校园跑腿用户端 3校园跑腿骑手端 4其他"),
     *   @OA\Property(property="status", type="integer", description="0关闭1小程序2公众号3全部"),
     *   @OA\Property(property="type", type="integer", description=""),
     * )
     */
    public function actionSetting()
    {
        $params = $this->queryMapPost();
        if (empty(SysTplMsg::$defaultCode[$params['code']]) || empty($params['scene'])) {
            return $this->fail('模板标识或场景无效');
        }
        $model = SysTplMsg::find()->where(['code'=>$params['code'], 'scene'=>$params['scene'], 'owner_id'=>$this->current_owner_id])->one();
        if (!$model) {
            $model = new SysTplMsg();
        }

        $cm = Yii::createObject([
            'class' => Wechat::className(),
            'owner_id' => $this->current_owner_id,
            'user_id' => $this->current_user_id,
            'scene' => $params['scene'],
        ]);
        $config = $cm->config();

        print_r($params);exit();
        if ($cm->app_type == Mini::TYPE_WX_MINI) { // 小程序
            $modelLitTpl = new Newtmpl($config);
            if ($model->wx_mini_tpl_id) {
                $modelLitTpl->delTemplate($model->wx_mini_tpl_id);
            }
            $data = $modelLitTpl->addTemplate(SysTplMsg::$defaultCode[$params['code']]['wx_mini_tpl_no'],SysTplMsg::$defaultCode[$params['code']]['wx_mini_tpl_kids'],SysTplMsg::$defaultCode[$params['code']]['wx_mini_tpl_scene']);
            $params['wx_mini_content'] = $data['priTmplId'];
        }

        if ($cm->app_type == Mini::TYPE_WX_OFFICIAL || $cm->app_type == Mini::TYPE_WX_SUBSCRIBE) { // 公众号
            $modelTpl = new Template($config);
            if ($model->wx_tpl_id) {
                $modelTpl->delPrivateTemplate($model->wx_tpl_id);
            }
            // $data = $modelTpl->addTemplate(SysTplMsg::$defaultCode[$params['code']]['wx_mini_tpl_no']);
            // $params['wx_tpl_id'] = $data['template_id'];
        }

        if ($data['errcode'] != 0) {
            return $this->fail($data);
        }


        return $model->saveData($params) ? $this->success():$this->fail($model->errors);
    }

    /**
     * @OA\Get(path="/backend/sys-tpl-msg/sys",
     *   summary="系统系统模板消息",
     *   tags={"backend模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     */
    public function actionSys()
    {
        return $this->success(SysTplMsg::$defaultCode);
    }
}
