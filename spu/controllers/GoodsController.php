<?php
namespace bricksasp\spu\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\spu\models\Goods;
use yii\data\ActiveDataProvider;
use bricksasp\models\redis\Token;
use bricksasp\models\FileRelation;
use bricksasp\models\LabelRelation;
use bricksasp\base\BackendController;
use bricksasp\spu\models\GoodsProduct;
use bricksasp\spu\models\GoodsComment;

/**
 * GoodsController implements the CRUD actions for Goods model.
 */
class GoodsController extends BackendController
{
    public function noLoginAction()
    {
        return [
            'index',
            'view'
        ];
    }

    public function checkLoginAction()
    {
        return [
            'index',
            'view'
        ];
    }

    /**
     * @OA\Get(path="/spu/goods/index",
     *   summary="商品列表",
     *   tags={"spu模块"},
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
     *         @OA\Schema(ref="#/components/schemas/goodsUpdate"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->get();
        $query = Goods::find();
        
        $query->andFilterWhere($this->ownerCondition());
        $query->andFilterWhere(['is_delete'=> empty($params['is_delete']) ? 0 : 1]);
        $query->orFilterWhere(['like', 'name', $params['name']??null]);
        $query->orFilterWhere(['like', 'brief', $params['brief']??null]);
        $query->orFilterWhere(['like', 'keywords', $params['keywords']??null]);

        if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND) {
            $query->select(['id', 'name', 'brief', 'video', 'price', 'costprice', 'mktprice','view_num','buy_num','on_shelves']);
        }
        $with= ['labels','cover'];
        $query->with($with);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $list = [];
        foreach ($dataProvider->models as $item) {
            $row = $item->toArray();
            $row['labels'] = $item->labels;
            $row['cover'] = $item->cover;
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
     * @OA\Get(path="/spu/goods/view",
     *   summary="商品详情",
     *   tags={"spu模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="id"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(ref="#/components/schemas/goodsUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $field = ['id','type', 'spec_id', 'brand_id', 'name', 'brief', 'content', 'params', 'specs', 'comments_num', 'view_num', 'comments_num','video','image_id', 'stock_unit', 'weight_unit', 'volume_unit'];
        $goods = Goods::find()
            ->with(['product', 'labels', 'brand', 'images', 'cover', 'video'])
            ->select($this->current_login_type == Token::TOKEN_TYPE_FRONTEND ? $field : [])
            ->where(['id'=>Yii::$app->request->get('id')])
            ->one();
        if (!$goods) Tools::breakOff(40001);

        $data = $goods->toArray();
        if ($goods->specs) {//是否为多规格
            $data['is_spec'] = 1;
            $data['specs'] = $goods->specs ? json_decode($goods->specs, true) : [];

            foreach ($goods->product as $k => $v) {
                $data['product'][$v->spec?$v->spec:$k] = $v->toArray();
            }
        }else{
            $data['is_spec'] = 0;
        }

        $data['labels'] = $goods->labels ? $goods->labels : [];
        $data['brand'] = $goods->brand ? $goods->brand : [];
        $data['images'] = $goods->images ? $goods->images : [];
        $data['cover'] = $goods->cover ? $goods->cover : [];
        $data['video'] = $goods->video ? $goods->video : [];
        $data['params'] = $goods->params ? json_decode($goods->params, true) : [];

        return $this->success($data);
    }

    /**
     * @OA\Post(path="/spu/goods/create",
     *   summary="添加商品",
     *   description="数据结构goodsCreate",
     *   tags={"spu模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/goodsCreate"
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
     * ),
     *
     * @OA\Schema(
     *   schema="goodsCreate",
     *   description="商品添加",
     *   @OA\Property(property="code", type="string", description="商品编码"),
     *   @OA\Property(property="barcode", type="string", description="商品条码"),
     *   @OA\Property(property="name", type="string", description="商品名称"),
     *   @OA\Property(property="subtitle", type="string", description="副标题"),
     *   @OA\Property(property="subtitle_short", type="string", description="短标题"),
     *   @OA\Property(property="brief", type="string", description="商品简介"),
     *   @OA\Property(property="keywords", type="string", description="关键词"),
     *   @OA\Property(property="brand_id", type="integer", description="品牌id"),
     *   @OA\Property(property="cat_id", type="integer", description="商品分类id"),
     *   @OA\Property(property="spec_id", type="integer", description="启用规格/规格id"),
     *   @OA\Property(property="type", type="integer", description="1实体商品2虚拟商品3虚拟物品4批发商品5计次/时商品"),
     *   @OA\Property(property="image_id", type="string", description="封面图"),
     *   @OA\Property(property="video", type="string", description="商品视频介绍id"),
     *   @OA\Property(property="content", type="string", description="商品详情"),
     *   @OA\Property(property="specs", type="string", description="规格详情"),
     *   @OA\Property(property="params", type="string", description="参数详情"),
     *   @OA\Property(property="sort", type="integer", description="排序"),
     *   @OA\Property(property="is_hot", type="integer", description="是否热门"),
     *   @OA\Property(property="is_recommend", type="integer", description="是否推荐"),
     *   @OA\Property(property="status", type="integer", description="1显示"),
     *   @OA\Property(property="check_stock", type="integer", description="1拍下减库存2付款减库存3不减库存"),
     *   @OA\Property(property="show_stock", type="integer", description="1显示库存"),
     *   @OA\Property(property="pre_sale", type="integer", description="1预售"),
     *   @OA\Property(property="on_shelves", type="integer", description="0未上架1上架2下架"),
     *   @OA\Property(property="on_shelves_at", type="integer", description="上架时间"),
     *   @OA\Property(property="off_shelves_at", type="integer", description="下架时间"),
     *   @OA\Property(property="sell_num", type="integer", description="已售数量"),
     *   @OA\Property(property="buy_num", type="integer", description="购买数"),
     *   @OA\Property(property="show_buy_num", type="integer", description="1显示购买数量"),
     *   @OA\Property(property="return_num", type="integer", description="退货数量"),
     *   @OA\Property(property="view_num", type="integer", description="浏览数"),
     *   @OA\Property(property="comments_num", type="integer", description="评论数"),
     *   @OA\Property(property="stock_unit", type="string", description="库存单位"),
     *   @OA\Property(property="weight_unit", type="string", description="重量单位"),
     *   @OA\Property(property="volume_unit", type="string", description="体积单位"),
     *   @OA\Property(property="price", type="number", description="价格"),
     *   @OA\Property(property="costprice", type="number", description="成本价"),
     *   @OA\Property(property="mktprice", type="number", description="市场价"),
     *   @OA\Property(property="distprice", type="number", description="分销价格"),
     *   @OA\Property(property="is_vip_discount", type="integer", description="1参与会员折扣"),
     *   @OA\Property(property="vip_discount", type="string", description="折扣0.1-10"),
     *   @OA\Property(property="vip_price", type="number", description="会员价格"),
     *   @OA\Property(property="vip", type="integer", description="会员限购等级"),
     *   @OA\Property(property="share_title", type="string", description="分享标题"),
     *   @OA\Property(property="share_image_id", type="string", description="分享图片"),
     *   @OA\Property(property="share_desc", type="string", description="分享描述"),
     *   @OA\Property(property="follow_force", type="integer", description="1购买强制关注"),
     *   @OA\Property(property="follow_guide", type="string", description="关注引导 跳转链接"),
     *   @OA\Property(property="follow_tip", type="string", description="未关注提示"),
     *   @OA\Property(property="offline_write_off", type="integer", description="线下核销1支持"),
     *   @OA\Property(property="store_force", type="integer", description="强制选择核销门店1是"),
     *   @OA\Property(property="store_id", type="string", description="门店id"),
     *   @OA\Property(property="write_off_at", type="string", description="兑换限时"),
     *   @OA\Property(property="write_off_at_type", type="integer", description="兑换限时类型1指定天数2指定日期"),
     *   @OA\Property(property="postage_free_num", type="integer", description="单品满件包邮"),
     *   @OA\Property(property="postage_free_price", type="number", description="单品满额包邮"),
     *   @OA\Property(property="exclude_area", type="string", description="不参与单品包邮地区"),
     *   @OA\Property(property="postage_id", type="integer", description="运费模版id"),
     *   @OA\Property(property="postage_price", type="integer", description="统一邮费"),
     *   @OA\Property(property="place_delivery", type="integer", description="发货地areaid"),
     *   @OA\Property(property="imageItems", type="array", description="图片数组",items={}),
     *   @OA\Property(property="labelItems", type="array", description="标签数组",items={}),
     *   @OA\Property(property="productItems", type="array", description="单品数组 查看goodsProduct单品结构",items={}),
     *   @OA\Property(property="specItems", type="array", description="规格详情",items={}),
     *   @OA\Property(property="paramItems", type="array", description="参数详情",items={}),
     * )
     *
     * 
     * @OA\Schema(
     *   schema="goodsProduct",
     *   description="单品结构",
     *   @OA\Property(property="name", type="string", description="单品名称"),
     *   @OA\Property(property="code", type="string", description="编码"),
     *   @OA\Property(property="barcode", type="string", description="条码"),
     *   @OA\Property(property="price", type="number", description="价格"),
     *   @OA\Property(property="costprice", type="number", description="成本价"),
     *   @OA\Property(property="mktprice", type="number", description="市场价"),
     *   @OA\Property(property="distprice", type="number", description="分销价格"),
     *   @OA\Property(property="is_vip_discount", type="integer", description="1参与会员折扣"),
     *   @OA\Property(property="vip_discount", type="number", description="折扣0.1-10"),
     *   @OA\Property(property="vip_price", type="number", description="会员价格"),
     *   @OA\Property(property="vip", type="integer", description="会员限购等级"),
     *   @OA\Property(property="on_shelves", type="integer", description="0未上架1上架2下架"),
     *   @OA\Property(property="spec", type="string", description="规格"),
     *   @OA\Property(property="stock", type="integer", description="库存"),
     *   @OA\Property(property="freeze_stock", type="integer", description="冻结库存"),
     *   @OA\Property(property="is_default", type="integer", description="1默认展示"),
     *   @OA\Property(property="weight", type="number", description="重量"),
     *   @OA\Property(property="volume", type="number", description="体积"),
     *   @OA\Property(property="imageItems", type="array", description="图片数组",items={}),
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new Goods();

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/spu/goods/update",
     *   summary="更新商品",
     *   description="数据结构 goodsUpdate",
     *   tags={"spu模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/goodsUpdate"
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
     * ),
     *
     * @OA\Schema(
     *   schema="goodsUpdate",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/goodsCreate"),
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
     * @OA\Post(path="/spu/goods/delete",
     *   summary="商品删除",
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
     *
     */
    public function actionDelete()
    {
        $params = $this->queryMapPost();
        if (Goods::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }

        $pids = GoodsProduct::find()->select(['id'])->where(['goods_id'=>$params['ids']??0])->asArray()->all();
        GoodsProduct::deleteAll(['goods_id'=>$params['ids']??0]);
        FileRelation::deleteAll(['object_id'=>$params['ids']??0, 'type'=>FileRelation::TYPE_GOODS]);
        FileRelation::deleteAll(['object_id'=>array_column($pids,'id'), 'type'=>FileRelation::TYPE_PRODUCT]);
        return Goods::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }

    /**
     * Finds the Goods model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Goods the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Goods::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
    }
}
