<?php
namespace bricksasp\spu\models;



use bricksasp\rbac\models\redis\Token;
use Yii;
use bricksasp\base\Tools;
use bricksasp\models\Brand;
use bricksasp\models\Label;

/**
 * This is the model class for table "{{%goods}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property int|null $version
 * @property string|null $gcode 商品编码
 * @property string|null $barcode 商品条码
 * @property string|null $name 商品名称
 * @property string $subtitle 副标题
 * @property string|null $subtitle_short 短标题
 * @property string|null $brief 简介
 * @property string|null $keywords
 * @property int|null $check_stock 1拍下减库存2付款减库存3不减库存
 * @property int|null $show_stock 1显示库存
 * @property int|null $brand_id 品牌
 * @property int|null $cat_id 分类
 * @property int|null $spec_id 启用规格/规格id
 * @property int|null $type 1实体商品2虚拟商品3虚拟物品4批发商品5计次/时商品
 * @property int|null $pre_sale 1预售
 * @property int|null $is_on_shelves 1上架2下架
 * @property int|null $on_shelves_at 上架时间
 * @property int|null $off_shelves_at 下架时间
 * @property string|null $image_id 封面图
 * @property string|null $video 视频
 * @property int|null $comments_count 评论数
 * @property int|null $browse_count 浏览数
 * @property int|null $buy_count 购买数
 * @property int|null $return_count 退货数量
 * @property int|null $sort
 * @property int|null $is_recommend
 * @property int|null $is_hot
 * @property int|null $status 1显示
 * @property string|null $content
 * @property string|null $specs
 * @property int|null $sell_count 已收数量
 * @property int|null $show_sell_count 1显示已售数量
 * @property string|null $params
 * @property string|null $stock_unit 库存单位
 * @property string|null $weight_unit
 * @property string|null $volume_unit
 * @property float|null $price 售价
 * @property float|null $costprice 成本
 * @property float|null $mktprice 市场价
 * @property int|null $is_delete
 * @property float|null $distprice 分销价格
 * @property int|null $is_vip_discount 1参与会员折扣
 * @property string|null $vip_discount 折扣0.1-10
 * @property float|null $vip_price 会员价格
 * @property int|null $vip 会员等级
 * @property string|null $share_title 分享标题
 * @property string|null $share_image_id 分享图片
 * @property string|null $share_desc 分享描述
 * @property int|null $follow_force 1购买强制关注
 * @property string|null $follow_guide 关注引导 跳转链接
 * @property string|null $follow_tip 未关注提示
 * @property int|null $offline_write_off 线下核销1支持
 * @property int|null $store_force 强制选择核销门店1是
 * @property string|null $store_id 门店id
 * @property string|null $write_off_at 兑换限时
 * @property int|null $write_off_at_type 兑换限时类型1指定天数2指定日期
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Goods extends \bricksasp\base\BaseActiveRecord
{
    const GOODS_ON_SHELVES_NOT = 0; //未上架
    const GOODS_ON_SHELVES_YES = 1; //上架
    const GOODS_ON_SHELVES_NO = 2; //下架
    const GOODS_ON_SHELVES_WAIT = 3; //待审核
    const GOODS_ON_SHELVES_REFUSE = 4; //已拒绝

    const GOODS_IS_NORMAL = 1; //实体商品
    const GOODS_IS_VIRTUAL = 2; //虚拟商品

    const IS_ONLINE_YES = 1;//是否支持线上发货 1支持
    const IS_ONLINE_NO = 2; //是否支持线上发货 2不支持

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods}}';
    }

    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
            \bricksasp\common\VersionBehavior::className(),
            [
                'class' => \bricksasp\common\SnBehavior::className(),
                'attribute' => 'gn',
                'type' => \bricksasp\common\SnBehavior::SN_GOODS,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['owner_id', 'user_id', 'version', 'check_stock', 'show_stock', 'brand_id', 'cat_id', 'spec_id', 'type', 'pre_sale', 'is_on_shelves', 'on_shelves_at', 'off_shelves_at', 'comments_count', 'browse_count', 'buy_count', 'return_count', 'sort', 'is_recommend', 'is_hot', 'status', 'sell_count', 'show_sell_count', 'is_delete', 'is_vip_discount', 'vip', 'follow_force', 'offline_write_off', 'store_force', 'write_off_at_type', 'created_at', 'updated_at'], 'integer'],
            [['subtitle'], 'required'],
            [['content', 'specs', 'params'], 'string'],
            [['price', 'costprice', 'mktprice', 'distprice', 'vip_price'], 'number'],
            [['gcode', 'barcode'], 'string', 'max' => 30],
            [['name', 'subtitle', 'brief', 'keywords', 'share_desc', 'follow_guide', 'store_id'], 'string', 'max' => 255],
            [['subtitle_short', 'write_off_at'], 'string', 'max' => 32],
            [['image_id', 'video', 'share_title', 'share_image_id', 'follow_tip'], 'string', 'max' => 64],
            [['stock_unit', 'weight_unit', 'volume_unit', 'vip_discount'], 'string', 'max' => 8],

            [['name', 'cat_id'],'required'],
            [['comments_count', 'browse_count', 'buy_count', 'return_count', 'sort'], 'default', 'value' => 0],

            [['is_nomal_virtual', 'status'], 'default', 'value' => 1],

            [['is_stock_check', 'is_on_shelves'], 'default', 'value' => 1],
            [['price','costprice','mktprice'], 'compare', 'compareValue' => 0, 'operator' => '>='],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner_id' => 'Owner ID',
            'user_id' => 'User ID',
            'version' => 'Version',
            'gcode' => 'Gcode',
            'barcode' => 'Barcode',
            'name' => 'Name',
            'subtitle' => 'Subtitle',
            'subtitle_short' => 'Subtitle Short',
            'brief' => 'Brief',
            'keywords' => 'Keywords',
            'check_stock' => 'Check Stock',
            'show_stock' => 'Show Stock',
            'brand_id' => 'Brand ID',
            'cat_id' => 'Cat ID',
            'spec_id' => 'Spec ID',
            'type' => 'Type',
            'pre_sale' => 'Pre Sale',
            'is_on_shelves' => 'Is On Shelves',
            'on_shelves_at' => 'On Shelves At',
            'off_shelves_at' => 'Off Shelves At',
            'image_id' => 'Image ID',
            'video' => 'Video',
            'comments_count' => 'Comments Count',
            'browse_count' => 'Browse Count',
            'buy_count' => 'Buy Count',
            'return_count' => 'Return Count',
            'sort' => 'Sort',
            'is_recommend' => 'Is Recommend',
            'is_hot' => 'Is Hot',
            'status' => 'Status',
            'content' => 'Content',
            'specs' => 'Specs',
            'sell_count' => 'Sell Count',
            'show_sell_count' => 'Show Sell Count',
            'params' => 'Params',
            'stock_unit' => 'Stock Unit',
            'weight_unit' => 'Weight Unit',
            'volume_unit' => 'Volume Unit',
            'price' => 'Price',
            'costprice' => 'Costprice',
            'mktprice' => 'Mktprice',
            'is_delete' => 'Is Delete',
            'distprice' => 'Distprice',
            'is_vip_discount' => 'Is Vip Discount',
            'vip_discount' => 'Vip Discount',
            'vip_price' => 'Vip Price',
            'vip' => 'Vip',
            'share_title' => 'Share Title',
            'share_image_id' => 'Share Image ID',
            'share_desc' => 'Share Desc',
            'follow_force' => 'Follow Force',
            'follow_guide' => 'Follow Guide',
            'follow_tip' => 'Follow Tip',
            'offline_write_off' => 'Offline Write Off',
            'store_force' => 'Store Force',
            'store_id' => 'Store ID',
            'write_off_at' => 'Write Off At',
            'write_off_at_type' => 'Write Off At Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getProduct()
    {
        return $this->hasMany(Product::className(), ['goods_id' => 'id']);
    }

    public function getBrand()
    {
        return $this->hasOne(Brand::className(), ['id' => 'brand_id'])->select(['id', 'name', 'logo']);
    }

    /**
     * 商品图片
     */
    public function getImageRelation()
    {
        return $this->hasMany(GoodsImage::className(), ['goods_id' => 'id']);
    }

    public function getFile(){
        return $this->hasMany(File::className(),['id'=>'image_id'])->via('imageRelation');
    }

    public function getLabels()
    {
        return $this->hasMany(GoodsLabel::className(), ['goods_id' => 'id']);
    }

    public function getLabelItems()
    {
        return $this->hasMany(Label::className(), ['id' => 'lable_id'])->via('labels')->select(['id', 'name', 'style']);
    }

    public function getCommentItems()
    {
        return $this->hasMany(GoodsComment::className(), ['goods_id' => 'id'])/*->onCondition(['cat_id' => 1])*/;
    }

    /**
     * 添加商品
     * @param  array  $data 
     * @return bool
     */
    public function saveGoods($data=[])
    {
        list($data, $productItems, $imageItems) = $this->formatData($data);
        if($data['login_type'] == Token::TOKEN_TYPE_FRONTEND){
            $data['is_on_shelves'] = self::GOODS_ON_SHELVES_NOT;
        }
        $this->load($data,'');
        if(empty($imageItems)){
            Tools::breakOff('商品图片不能为空');
        }
        if(empty($productItems)){
            Tools::breakOff('商品规格不能为空');
        }
        $transaction = self::getDb()->beginTransaction();
        try {
            $this->specs = json_encode($this->getGoodsSpec($this->type_id, 2),JSON_UNESCAPED_UNICODE);
            $this->params = json_encode($this->getParams($this->type_id),JSON_UNESCAPED_UNICODE);

            $this->save();
            if (!$this->id) {
                $transaction->rollBack();
                return false;
            }

            $images = [];
            foreach ($imageItems as $k => $v) {
                $image['goods_id'] = $this->id;
                $image['image'] = $v['id'];
                $image['file_url'] = $v['file_url'];
                $image['sort'] = $k + 1;
                $images[] = $image;
            }

            self::getDb()->createCommand()
            ->batchInsert(GoodsImage::tableName(),['goods_id','image','file_url','sort'],$images)
            ->execute();

            foreach ($productItems as $k => $product) {
                $product['goods_id']    = $this->id;
                $product['is_on_shelves']    = $this->is_on_shelves;
                $model = new Product();
                $model->load($product,'');
                $model->save();
            }
            $transaction->commit();
            return true;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            Tools::breakOff($e->getMessage());
            return false;
        }
    }

    /**
     * 更新视频
     * @param  array  $data 
     * @return bool
     */
    public function updateGoods($data=[])
    {
        $oldAttributes = $this->oldAttributes;
        list($data, $productItems, $imageItems) = $this->formatData($data);
        $this->load($data,'');
        if(empty($imageItems)){
            Tools::breakOff('商品图片不能为空');
        }
        if(empty($productItems)){
            Tools::breakOff('商品规格不能为空');
        }
        $transaction = self::getDb()->beginTransaction();
        try {
            if ($this->type == self::SPEC_PRODUCT && $oldAttributes['type_id'] != $this->type_id) {
                $this->specs = json_encode($this->getGoodsSpec($this->type_id, 2),JSON_UNESCAPED_UNICODE);
                $this->params = json_encode($this->getParams($this->type_id),JSON_UNESCAPED_UNICODE);
            }
            // 更改类型删除所有单品
            Product::deleteAll(['goods_id'=>$this->id]);
            // 保存商品
            if ($this->save() === false) {
                $transaction->rollBack();
                return false;
            }
            $images = [];
            foreach ($imageItems as $k => $v) {
                $image['goods_id'] = $this->id;
                $image['image'] = $v['id'];
                $image['file_url'] = $v['file_url'];
                $image['sort'] = $k + 1;
                $images[] = $image;
            }

            GoodsImage::deleteAll(['goods_id'=>$this->id]);
            self::getDb()->createCommand()
            ->batchInsert(GoodsImage::tableName(),['goods_id','image','file_url','sort'],$images)
            ->execute();
            // 保存单品
            foreach ($productItems as $product) {
                if (empty($product['id'])) {
                    $product['goods_id']    = $this->id;
                    $product['is_on_shelves']    = $this->is_on_shelves;
                    $model = new Product();
                    $model->load($product,'');
                    $model->save();
                }else{
                    $model = Product::findOne($product['id']);
                    if($model){
                        $model->load($product, '');
                        $model->save();
                    }
                }
            }
            $transaction->commit();
            return true;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            Tools::breakOff($e->getMessage());
        }
        return false;
    }

    public function formatData($data)
    {
        $productItems = $data['productItems'];
        $imageItems = $data['imageItems'];
        $videoItem = $data['videoItem'];
        if(empty($imageItems)){
            Tools::breakOff('商品图片不能为空');
        }
        if(empty($productItems)){
            Tools::breakOff('商品规格不能为空');
        }
        if(isset($data['cat_id']) && is_array($data['cat_id'])){
            $data['cat_id'] = end($data['cat_id']);
        }
        unset($data['productItems'], $data['imageItems'], $data['videoItem']);

        if ($data['is_on_shelves'] == 1 && $this->is_on_shelves != 1) {
            $data['on_shelves_time']= time();
        }elseif ($data['is_on_shelves'] == 2 && $this->is_on_shelves != 2) {
            $data['off_shelves_time'] = time();
        }
        
        // 设置默认单品
        $is_default = array_column($productItems, 'is_default');
        if (!in_array(1, $is_default)) {
            $productItems[0]['is_default'] = 1;
            $k = 0;
        }else{
            $k = array_search(1, $is_default);
        }
        $data['price'] = $productItems[$k]['price'];
        $data['costprice'] = $productItems[$k]['costprice'];
        $data['mktprice'] = $productItems[$k]['mktprice'];
        $data['image'] = (isset($data['image']) && !empty($data['image'])) ?$data['image']:($imageItems[0]['id'] ??'') ;
        return [$data, $productItems, $imageItems];
    }

    /**
     * 商品规格属性
     * @param  integer $type_id 类型id
     * @param  integer $sence 1 product 2 goods
     * @return array           
     */
    public static function getGoodsSpec($type_id=0 , $sence=1)
    {
        $item_spec = Type::find()->where(['id'=> $type_id])->one();
        if (!$item_spec) return null;
        $input = [];
        $specValues = Spec::find()->with(['items'])->where(['id'=>explode(',',$item_spec->spec)])->all();

        if ($sence == 2) {
            foreach ($specValues as $spec) {
                foreach ($spec->items as $val) {
                    $input[$spec['name']][] = $val['value'];
                }
            }
            return $input;
        }

        foreach ($specValues as $spec) {
            $spec_v = [];
            foreach ($spec->items as $val) {
                $spec_v[$val['value']] = $spec['name'];
            }
            $input[] = $spec_v;
        }
        $input = array_filter($input);
        if (empty($input)) Tools::breakOff('类型数据不全');
        // 拼接格式
        $spec_product = Tools::cartesian($input,function ($v,$v2=[])
        {
            if ($v2) {
                return $v[1] . ',' . $v2[1] . ':' . $v2[0];
            }
            return $v[1] . ':' . $v[0];
        });
        return $spec_product;
    }

    /**
     * 商品参数
     * @param  integer $type_id 类型id
     * @return array           
     */
    public static function getParams($type_id=0)
    {
        $item_parmas = Type::find()->where(['id'=> $type_id])->one();
        $params = Params::find()->where(['id'=>explode(',',$item_parmas['params'])])->all();
        $output = [];
        foreach ($params as $v) {
            $output[$v['name']] = json_decode($v['value'],true);
        }
        return $output;
    }

    /**
     * 商品详情
     * @OA\Schema(
     *  schema="goodsDetail",
     *  description="商品详情结构",
     *  @OA\Property(property="name", type="string", description="商品名称"),
     *  @OA\Property(property="brief", type="string", description="商品简介" ),
     *  @OA\Property(property="content", type="string", description="商品内容" ),
     *  @OA\Property(property="comments_count", type="integer", description="评论数" ),
     *  @OA\Property(property="view_count", type="integer", description="浏览数" ),
     *  @OA\Property(property="stock_unit", type="integer", description="库存单位" ),
     *  @OA\Property(property="weight_unit", type="integer", description="重量单位" ),
     *  @OA\Property(property="volume_unit", type="integer", description="体积单位" ),
     *  @OA\Property(property="price", type="string", description="售价"),
     *  @OA\Property(property="costprice", type="string", description="成本价" ),
     *  @OA\Property(property="mktprice", type="string", description="原价" ),
     *  @OA\Property(property="imageItems", type="array", description="商品图片", @OA\Items()),
     *  @OA\Property(property="labelItems", type="array", description="商品标签", @OA\Items(ref="#/components/schemas/label")),
     *  @OA\Property(property="brandItem", description="品牌", ref="#/components/schemas/brandUpdate"),
     *  @OA\Property(property="videoItem", description="视频介绍", ),
     *  @OA\Property(property="default_product", description="默认单品", ref="#/components/schemas/product"),
     * )
     *  @1OA\Property(property="params", description="商品 参数名称-值", ref="#/components/schemas/params"),
     *  @1OA\Property(property="specs", description="商品 属性名称-值", ref="#/components/schemas/specs"),
     */
    public static function goodsDetail($map,$product_id=0, $all=2)
    {
        $goods = Goods::find()
            ->with(['productItems', 'labelItems', 'brandItem', 'imageItems', 'videoItem'])
            ->select(['id','type', 'type_id', 'brand_id', 'name', 'brief', 'content', 'params', 'comments_count', 'view_count', 'comments_count','video','image', 'stock_unit', 'weight_unit', 'volume_unit'])
            ->where($map)
            ->one();
        if (!$goods) Tools::exceptionBreak(Yii::t('base',930003));

        if ($goods->type == self::SPEC_PRODUCT) {
            if ($product_id) {
                $k = array_search($product_id, array_column($goods->productItems, 'id'));
            }else {
                $k = array_search(1, array_column($goods->productItems, 'is_default'));
            }
            $default_product = $goods->productItems[$k];
            $data['default_product'] = $default_product;
            $data['specs'] = self::defaultSpec($goods->productItems,$default_product->specs);
        }else{
            $data['default_product'] = $goods->productItems[0];
            $data['specs'] = [];
        }
        $data = array_merge($goods->toArray(), $data);
        $data['params'] = self::getParams($goods->type_id);
        $data['labelItems'] = $goods->labelItems;
        $data['brandItem'] = $goods->brandItem;
        $data['videoItem'] = $goods->videoItem ? Tools::format_array($goods->videoItem, ['file_url'=>['implode',['',[Config::instance()->web_url,'###']],'array']]) : (object)[];
        $imageItems = Tools::format_array($goods->imageItems, ['file_url'=>['implode',['',[Config::instance()->web_url,'###']],'array']], 2);

        $imgs = array_column($imageItems, 'id');
        $sort = array_column($goods->imageRelation, 'sort', 'image');
        $k = [];

        foreach ($imgs as $v) {
            $k[] = $sort[$v];
        }

        $imageItems = array_combine($k, $imageItems);
        ksort($imageItems);
        $data['imageItems'] = array_values($imageItems);
        $data['content'] = str_replace('src="file', 'src="' . Config::instance()->web_url . '/file', $data['content']);
        if ($all == 1) {
            $data['product_list'] = $goods->productItems;
        }
        return $data;
    }

    /**
     * 商品默认属性
     * @param  array $products 
     * @return array 
     */
    public static function defaultSpec($data=[],$default_specs=[])
    {
        $spes = [];
        foreach ($data as $product) {
            $item = explode(',', $product->specs);
            foreach ($item as $v) {
                $kv = explode(':', $v);
                $spec['product_id'] = $product->id;
                $spec['spec'] = $kv[1];
                if ($default_specs == $product->specs) $spec['default'] = 1; else $spec['default'] = 0;
                $spes[$kv[0]][] = $spec;
            }
        }
        return $spes;
    }
}
