<?php

namespace bricksasp\backend\controllers;

use Yii;
use bricksasp\models\SmsTpl;
use yii\data\ActiveDataProvider;
use bricksasp\base\BackendController;
use bricksasp\base\Tools;

/**
 * SmsTplController implements the CRUD actions for SmsTpl model.
 */
class SmsTplController extends BackendController
{
    /**
     * @OA\Get(path="/backend/sms-tpl/index",
     *   summary="短信模板列表",
     *   tags={"backend模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
     *   @OA\Parameter(name="is_delete",in="query",@OA\Schema(type="integer"),description="1软删除"),
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
        $query = SmsTpl::find();
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
     * @OA\Get(path="/backend/sms-tpl/view",
     *   summary="短信模板详情",
     *   tags={"backend模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *       @OA\Property(property="platform", type="integer", description="platform"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(ref="#/components/schemas/SmsTplUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel($this->updateCondition(['platform'=>$params['platform'] ?? 0]));
        return $this->success($model);
    }

    /**
     * @OA\Post(path="/backend/sms-tpl/create",
     *   summary="创建短信模板",
     *   tags={"backend模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/SmsTplCreate"
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
     *   schema="SmsTplCreate",
     *   description="短信模板",
     *   @OA\Property(property="code", type="string", description="模版标识"),
     *   @OA\Property(property="content", type="string", description="模版内容"),
     *   @OA\Property(property="platform", type="integer", description="1腾讯2阿里"),
     *   @OA\Property(property="sign", type="integer", description="签名"),
     *   @OA\Property(property="tpl_id", type="integer", description="平台模版id号"),
     *   @OA\Property(property="appid", type="integer", description="appid"),
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new SmsTpl();

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/backend/sms-tpl/update",
     *   summary="修改短信模板",
     *   tags={"backend模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/SmsTplUpdate"
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
     * 
     * @OA\Schema(
     *   schema="SmsTplUpdate",
     *   description="短信模板数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="platform", type="integer", description="platform"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/SmsTplCreate"),
     *   }
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel($this->updateCondition(['platform'=>$params['platform'] ?? 0]));

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/backend/sms-tpl/delete",
     *   summary="删除短信模板",
     *   tags={"backend模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="ids", type="array", description="ids", @OA\Items()),
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
     */
    public function actionDelete()
    {
        $params = $this->queryMapPost();
        return SmsTpl::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }

    /**
     * Finds the SmsTpl model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SmsTpl the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SmsTpl::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
    }
}
