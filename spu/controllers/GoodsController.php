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

    /**
     * @OA\Post(path="/spu/goods/set-label",
     *   summary="设置商品标签",
     *   tags={"spu模块"},
     *
     *   @OA\Parameter(description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/goodsLabelSet"
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
     *
     *   @OA\Schema(
     *   schema="goodsLabelSet",
     *   description="设置标签",
     *   @OA\Property(property="id", type="integer", description="商品id"),
     *   @OA\Property(property="label", type="array", description="标签 [{id:2},{id:1}]",@OA\Items()),
     * )
     *
     *
     */
    public function actionSetlabel()
    {
        $data = unserialize($params);
        $goods_id = $data['id'];
        $inster = [];
        foreach ($data['label'] as $k => $item) {
            $row['goods_id'] = $goods_id;
            $row['lable_id'] = $item['id'];
            $row['sort'] = $k + 1;
            $inster[] = $row;
        }
        GoodsLabel::deleteAll(['goods_id' => $goods_id]);
        $a = GoodsLabel::getDb()->createCommand()
            ->batchInsert(GoodsLabel::tableName(),['goods_id','lable_id','sort'],$inster)
            ->execute();
        return $this->success($a);
    }

    /**
     * @OA\Post(path="/spu/goods/user-comment",
     *   summary="商品评价",
     *   tags={"spu模块"},
     *
     *   @OA\Parameter(description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/goodsUserComment"
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
     *
     *   @OA\Schema(
     *   schema="goodsUserComment",
     *   description="商品评论",
     *   @OA\Property(property="pid", type="integer", description="父级评价ID"),
     *   @OA\Property(property="order_id", type="string", description="订单id"),
     *   @OA\Property(property="goods_id", type="string", description="商品ID 关联goods.id"),
     *   @OA\Property(property="product_id", type="string", description="货品规格序列号存储"),
     *   @OA\Property(property="score", type="string", description="评价1-5星"),
     *   @OA\Property(property="content", type="string", description="评价内容"),
     *   @OA\Property(property="image_ids", type="array", description="评价图片[{image:image_id,file_url:xxx.com/xx.png,sort:1},{image:image_id,file_url:xxx.com/xx.png,sort:1}]",
     *     @OA\Items(
     *            @OA\Property(description="评论id",property="comment_id",type="string"),example=2131313,
     *            @OA\Property(description="图片ids",property="image_id",type="string"),example=2131313,
     *            @OA\Property(description="图片URL",property="file_url",type="string"),example=123,
     *            @OA\Property(description="排序",property="sort",type="string"),example=123,
     *           ),
     * ),
     * )
     *
     *
     */
    public function actionUserComment()
    {
        $params = unserialize($params);
        $params['status'] = GoodsComment::COMMENT_STATUS_NO;
        $validator = new FormValidate($params, ['scenario' => 'create_goods_comment']);
        if ($validator->validate()) {
            $model = new GoodsComment();
            if(!isset($params['avatar'])){
                $userInfo = UserInfo::findOne(['user_id'=>$params['user_id']]);
                $params['avatar'] = $userInfo['avatar']??'';
                $params['nickname'] = $userInfo['nickname']??'';
            }
            if(!isset($params['created_at'])){
                $params['created_at'] = time();
            }
            if($model->saveData($params)){
                return $this->success('评论成功');
            }
        }
        return $this->fail($validator->errors);
    }

    /**
     * 商品评论列表
     * @param $params
     * @return array
     */
    public function actionComment()
    {
        $params = unserialize($params);
        $goods_id = $params['goods_id'];

        $query = GoodsComment::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query
            ,
        ]);
        if($params['login_type'] == Token::TOKEN_TYPE_FRONTEND){
            $query->where(['status'=>GoodsComment::COMMENT_STATUS_YES]);
            $query->with(['imageItems', 'userInfo','goods']);
        }else{
            $query->with(['imageItems', 'userInfo','order','goods']);
        }
        $query->andFilterWhere([
            'goods_id' => $goods_id,
            'score' => $params['score']??null,
            'logistics_score' => $params['logistics_score']??null,
            'service_score' => $params['service_score']??null,
        ]);
        $query->andFilterWhere(['like','nickname' , $params['nickname']??null]);
        $query->andFilterWhere(['like','content' , $params['content']??null]);
        $query->orderBy(' sort desc ');
        $query->asArray();
        return $this->success([
            'list' => $dataProvider->models,
            'pageCount' => $dataProvider->pagination->pageCount,
            'totalCount' => $dataProvider->pagination->totalCount,
            'page' => $dataProvider->pagination->page + 1,
            'pageSize' => $dataProvider->pagination->limit,
        ]);

    }


    /**
     * @OA\Post(path="/spu/goods/comment-examine",
     *   summary="商品评价审核",
     *   tags={"spu模块"},
     *
     *   @OA\Parameter(description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *
     *  @OA\Parameter( description="评价id",name="id",in="query",@OA\Schema(type="integer")),
     *  @OA\Parameter( description="状态 0未审核 1审核通过 2未通过",name="status",in="query",@OA\Schema(type="integer")),
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
     *
     */
    public function actionCommentExamine(){
        $params = unserialize($params);
        if(!isset($params['created_at'])){
            $params['created_at'] = time();
        }else{
            $params['created_at'] = strtotime($params['created_at']);
        }
        $comment = GoodsComment::findOne(['id'=>$params['id']]);
        if(!$comment){
            return $this->fail('未找到评论数据');
        }
        if(!$comment->load($params) || !$comment->save()){
            return $this->fail('更新状态失败');
        }
        return $this->success([],'审核成功');
    }

    /**
     * @OA\Post(path="/spu/goods/comment-display",
     *   summary="商品评价审核",
     *   tags={"spu模块"},
     *
     *   @OA\Parameter(description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *
     *  @OA\Parameter( description="评价id",name="id",in="query",@OA\Schema(type="integer")),
     *  @OA\Parameter( description="是否显示 1显示 2不显示",name="status",in="query",@OA\Schema(type="integer")),
     *  @OA\Parameter( description="排序 值越大越靠前",name="sort",in="query",@OA\Schema(type="integer")),
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
     *
     */
    public function actionCommentDisplay(){
        $params = unserialize($params);
        $comment = GoodsComment::findOne(['id'=>$params['id']]);
        if(!$comment){
            return $this->fail('未找到评论数据');
        }
        if(!$comment->load($params) || !$comment->save()){
            return $this->fail('更新状态失败');
        }
        return $this->success([],'更新状态成功');
    }

    /**
     * @OA\Get(path="/spu/goods/goods-buy-user",
     *   summary="商品购买用户",
     *   tags={"spu模块"},
     *
     *  @OA\Parameter(description="商品id",name="goods_id",in="query",@OA\Schema(type="integer")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/buyUserList"),
     *     ),
     *   ),
     * ),
     *
     * @OA\Schema(
     *  schema="buyUserList",
     *  description="购买该商品的用户列表",
     *  @OA\Property(property="goods_id", type="integer", description="商品ID"),
     *  @OA\Property( property="avatar", type="string", description="头像"),
     *  @OA\Property( property="nickname", type="string", description="昵称" ),
     *  @OA\Property( property="created_at", type="integer", description="购买时间" ),
     * )
     *
     *
     */
    public function actionGetBuyUser(){
        $params = unserialize($params);
        $list = OrderItem::find()->select(['goods_id','user_id','created_at','id'])->with(['connect','userWx','userInfo'])->where(['goods_id'=>$params['goods_id']])->groupBy('user_id')->orderBy(' id desc ')->limit(3)->asArray()->all();
        if($list){
            foreach($list as $k=>$v){
                if(empty($list[$k]['userInfo'])){
                    unset($list[$k]);
                }
            }
        }
        return $this->success($list);
    }

    /**
     * @OA\Post(path="/spu/goods/examine",
     *   summary="商品上架审核",
     *   tags={"spu模块"},
     *
     *   @OA\Parameter(description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *
     *  @OA\Parameter( description="商品id",name="id",in="query",@OA\Schema(type="integer")),
     *  @OA\Parameter( description="0未上架1上架2下架3待审核4已拒绝",name="is_on_shelves",in="query",@OA\Schema(type="integer")),
     *  @OA\Parameter( description="拒绝原因",name="refuse_msg",in="query",@OA\Schema(type="string")),
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
     *
     */
    public function actionExamine(){
        $params = unserialize($params);
        $goods = Goods::findOne(['id'=>$params['id']]);
        if(!$goods){
            return $this->fail('未找到商品数据');
        }
        if($params['is_on_shelves'] == Goods::GOODS_ON_SHELVES_YES){
            $goods->is_on_shelves = Goods::GOODS_ON_SHELVES_YES;
            $goods->on_shelves_time = time();
        }elseif($params['is_on_shelves'] == Goods::GOODS_ON_SHELVES_NO){
            $goods->is_on_shelves = Goods::GOODS_ON_SHELVES_NO;
            $goods->off_shelves_time = time();
        }elseif($params['is_on_shelves'] == Goods::GOODS_ON_SHELVES_REFUSE){
            $goods->is_on_shelves = Goods::GOODS_ON_SHELVES_REFUSE;
            $goods->refuse_msg = $params['refuse_msg']??'';
        }
        if(!$goods->save()){
            return $this->fail('更新状态失败');
        }
        return $this->success([],'审核成功');
    }

    /**
     * @OA\Post(path="/spu/goods/apply-examine",
     *   summary="商品申请上架",
     *   tags={"spu模块"},
     *
     *   @OA\Parameter(description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *
     *  @OA\Parameter( description="商品id",name="id",in="query",@OA\Schema(type="integer")),
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
     *
     */
    public function actionApplyExamine(){
        $params = unserialize($params);
        $goods = Goods::findOne(['id'=>$params['id']]);
        if(!$goods){
            return $this->fail('未找到商品数据');
        }
        $goods->is_on_shelves = Goods::GOODS_ON_SHELVES_WAIT;
        if(!$goods->save()){
            return $this->fail('上架申请失败');
        }
        return $this->success([],'提交审核成功');
    }

    /**
     * @OA\Post(path="/spu/goods/cancel-apply-examine",
     *   summary="取消商品申请上架",
     *   tags={"spu模块"},
     *
     *   @OA\Parameter(description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *
     *  @OA\Parameter( description="商品id",name="id",in="query",@OA\Schema(type="integer")),
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
     *
     */
    public function actionCancelApplyExamine(){
        $params = unserialize($params);
        $goods = Goods::findOne(['id'=>$params['id']]);
        if(!$goods){
            return $this->fail('未找到商品数据');
        }
        $goods->is_on_shelves = Goods::GOODS_ON_SHELVES_NOT;
        if(!$goods->save()){
            return $this->fail('取消上架申请失败');
        }
        return $this->success([],'取消审核成功');
    }


}
