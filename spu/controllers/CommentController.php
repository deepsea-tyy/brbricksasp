<?php

namespace bricksasp\spu\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\spu\models\GoodsComment;
use yii\data\ActiveDataProvider;
use bricksasp\base\BackendController;
use bricksasp\models\redis\Token;

/**
 * CommentController implements the CRUD actions for GoodsComment model.
 */
class CommentController extends BackendController
{
    public function checkLoginAction()
    {
        return [
            'index',
        ];
    }

    public function noLoginAction()
    {
        return [
            'index',
        ];
    }

    /**
     * @OA\Get(path="/spu/comment/index",
     *   summary="商品评论列表",
     *   tags={"spu模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
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
     *         @OA\Schema(ref="#/components/schemas/goodsCommentUpdate"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->get();
        $query = GoodsComment::find();
        $query->andFilterWhere([
            'type' => $params['type']??null,
        ]);
        
        if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND && empty($params['user_data'])) {
            $query->andFilterWhere(['status' => 1]);
        }
        $query->andFilterWhere($this->ownerCondition());
        $query->andFilterWhere(['like', 'name', $params['name']??null]);
        $query->andFilterWhere(['is_delete'=> empty($params['is_delete']) ? 0 : 1]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query->with(['images']),
        ]);
        
        $list = [];
        foreach ($dataProvider->models as $item) {
            $row = $item->toArray();
            $row['images'] = $item->images ?? [];
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
     * @OA\Get(path="/spu/comment/view",
     *   summary="商品评论详情",
     *   tags={"spu模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="id"),
     *
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/goodsCommentUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $model = $this->findModel(Yii::$app->request->get('id'));
        $data = $model->toArray();
        $data['images'] = $model->images ?? [];
        return $this->success($data);
    }

    /**
     * @OA\post(path="/spu/comment/create",
     *   summary="添加商品评论",
     *   description="数据结构 goodsCommentCreate",
     *   tags={"spu模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/goodsCommentCreate"
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
     *   schema="goodsCommentCreate",
     *   description="商品评论",
     *   @OA\Property(property="score", type="integer", description="评价1-5星", example=1),
     *   @OA\Property(property="order_item_id", type="integer", description="order_item_id"),
     *   @OA\Property(property="goods_id", type="integer", description="",  example=null),
     *   @OA\Property(property="product_id", type="integer", description=""),
     *   @OA\Property(property="content", type="string", description="评价内容"),
     *   @OA\Property(property="seller_content", type="string", description="商家回复"),
     *   @OA\Property(property="status", type="integer", description="1显示2不显示"),
     *   @OA\Property(property="like", type="integer", description="点赞数"),
     *   @OA\Property(property="reply_num", type="integer", description="回复数"),
     *   @OA\Property(property="imageItems", type="array", description="图片数组",items={}),
     * )
     *
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new GoodsComment();

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\post(path="/spu/comment/update",
     *   summary="修改商品评论",
     *   description="数据结构 goodsCommentUpdate",
     *   tags={"spu模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/goodsCommentUpdate"
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
     *   schema="goodsCommentUpdate",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/goodsCommentCreate"),
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
     * @OA\Post(path="/spu/comment/delete",
     *   summary="删除商品评论",
     *   tags={"spu模块"},
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
        if (GoodsComment::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }
        return GoodsComment::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }

    /**
     * Finds the GoodsComment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return GoodsComment the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = GoodsComment::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
    }
}
