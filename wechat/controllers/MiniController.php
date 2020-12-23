<?php
namespace bricksasp\wechat\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\Mini;
use yii\data\ActiveDataProvider;
use bricksasp\models\redis\Token;
use bricksasp\base\BackendController;

/**
 * MiniController implements the CRUD actions for Mini model.
 */
class MiniController extends BackendController
{

    /**
     * @OA\Get(path="/wechat/mini/index",
     *   summary="微信公众号/小程序列表",
     *   tags={"wechat模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
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
     * 
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->get();
        $query = Mini::find();

        $query->andFilterWhere($this->ownerCondition());
        $query->andFilterWhere(['is_delete'=> empty($params['is_delete']) ? 0 : 1]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $list = [];
        foreach ($dataProvider->models as $item) {
            $row = $item->toArray();
            $row['file'] = $item->file ?? [];
            $list[] = $row;
        }
        return $this->success([
          'list' => $list,
          'pageCount' => $dataProvider->pagination->pageCount,
          'totalCount' => $dataProvider->pagination->totalCount,
          'page' => $dataProvider->pagination->page + 1,
          'pageSize' => $dataProvider->pagination->limit,
        ]);
    }

    /**
     * @OA\Get(path="/wechat/mini/view",
     *   summary="微信公众号/小程序详情",
     *   tags={"wechat模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="id"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/MiniUpdate"
     *       ),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel($params['id'] ?? 0);
        return $this->success($model);
    }

    /**
     * @OA\Post(path="/wechat/mini/create",
     *   summary="创建微信公众号/小程序",
     *   tags={"wechat模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/MiniCreate"
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
     *   schema="MiniCreate",
     *   description="微信公众号/小程序",
     *   @OA\Property(property="name", type="string", description="名称"),
     *   @OA\Property(property="avatar", type="string", description="头像"),
     *   @OA\Property(property="platform", type="integer", description="1微信2支付宝3抖音"),
     *   @OA\Property(property="appid", type="string", description="appid"),
     *   @OA\Property(property="app_secret", type="string", description="开发密钥"),
     *   @OA\Property(property="app_original_id", type="string", description="原始id"),
     *   @OA\Property(property="encoding_key", type="string", description="消息加密密钥"),
     *   @OA\Property(property="type", type="integer", description="微信1小程序2服务号3订阅号"),
     *   @OA\Property(property="status", type="integer", description="1启用"),
     *   @OA\Property(property="scene", type="integer", description="场景1默认"),
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new Mini();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/wechat/mini/update",
     *   summary="修改微信公众号/小程序",
     *   tags={"wechat模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/MiniUpdate"
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
     *   schema="MiniUpdate",
     *   description="微信公众号/小程序",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/MiniCreate"),
     *   }
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel($this->updateCondition(['id'=>$params['id'] ?? 0]));

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/wechat/mini/delete",
     *   summary="删除微信公众号/小程序",
     *   tags={"wechat模块"},
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
     *
     * 
     */
    public function actionDelete()
    {
        $params = $this->queryMapPost();
        if (Mini::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }
        return Mini::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }

    protected function findModel($id)
    {
        if (($model = Mini::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
    }
}
