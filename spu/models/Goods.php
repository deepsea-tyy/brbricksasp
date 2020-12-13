<?php

namespace bricksasp\spu\models;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\Brand;
use bricksasp\models\Label;
use bricksasp\models\LabelRelation;
use bricksasp\models\File;
use bricksasp\models\FileRelation;

/**
 * This is the model class for table "{{%goods}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property int|null $version
 * @property string|null $code 商品编码
 * @property string|null $barcode 商品条码
 * @property string|null $name 商品名称
 * @property string $subtitle 副标题
 * @property string|null $subtitle_short 短标题
 * @property string|null $brief 简介
 * @property string|null $keywords
 * @property int|null $brand_id 品牌
 * @property int|null $cat_id 分类
 * @property int|null $spec_id 启用规格/规格id
 * @property int|null $type 1实体商品2虚拟商品3虚拟物品4批发商品5计次/时商品
 * @property string|null $image_id 封面图
 * @property string|null $video 视频
 * @property string|null $content
 * @property string|null $specs
 * @property string|null $params
 * @property int|null $sort
 * @property int|null $is_hot
 * @property int|null $is_recommend
 * @property int|null $status
 * @property int|null $is_delete
 * @property int|null $check_stock 1拍下减库存2付款减库存3不减库存
 * @property int|null $show_stock 1显示库存
 * @property int|null $pre_sale 1预售
 * @property int|null $on_shelves 0未上架1上架2下架
 * @property int|null $on_shelves_at 上架时间
 * @property int|null $off_shelves_at 下架时间
 * @property int|null $sell_num 已售数量
 * @property int|null $buy_num 购买数
 * @property int|null $show_buy_num 1显示购买数量
 * @property int|null $return_num 退货数量
 * @property int|null $view_num 浏览数
 * @property int|null $comments_num 评论数
 * @property string|null $stock_unit 库存单位
 * @property string|null $weight_unit
 * @property string|null $volume_unit
 * @property float|null $price 售价
 * @property float|null $costprice 成本
 * @property float|null $mktprice 市场价
 * @property float|null $distprice 分销价格
 * @property int|null $is_vip_discount 1参与会员折扣
 * @property string|null $vip_discount 折扣0.1-10
 * @property float|null $vip_price 会员价格
 * @property int|null $vip 会员限购等级
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
    const ONSHELVES_DEF = 0; //未上架
    const ONSHELVES_ON = 1; //上架
    const ONSHELVES_OFF = 2; //下架
    const ONSHELVES_WAIT = 3; //待审核
    const ONSHELVES_REFUSE = 4; //已拒绝

    const TYPE_GOODS_NORMAL = 1; //实体商品
    const TYPE_GOODS_VIRTUAL = 2; //虚拟商品
    const TYPE_VIRTUAL = 3; //虚拟物品
    const TYPE_GOODS_WHOLESALE = 4; //批发商品
    const TYPE_GOODS_TIMER = 5; //计次/时商品

    const TYPE_CHECK_STOCK_ORDER = 1; //拍下减库存
    const TYPE_CHECK_STOCK_PAY = 2; //付款减库存
    const TYPE_CHECK_STOCK_NOT = 3; //不减库存

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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['owner_id', 'user_id', 'version', 'brand_id', 'cat_id', 'spec_id', 'type', 'sort', 'is_hot', 'is_recommend', 'status', 'is_delete', 'check_stock', 'show_stock', 'pre_sale', 'on_shelves', 'on_shelves_at', 'off_shelves_at', 'sell_num', 'buy_num', 'show_buy_num', 'return_num', 'view_num', 'comments_num', 'is_vip_discount', 'vip', 'follow_force', 'offline_write_off', 'store_force', 'write_off_at_type', 'created_at', 'updated_at'], 'integer'],
            [['subtitle'], 'required'],
            [['content', 'specs', 'params'], 'string'],
            [['price', 'costprice', 'mktprice', 'distprice', 'vip_price'], 'number'],
            [['code', 'barcode'], 'string', 'max' => 30],
            [['name', 'subtitle', 'brief', 'keywords', 'share_desc', 'follow_guide', 'store_id'], 'string', 'max' => 255],
            [['subtitle_short', 'write_off_at'], 'string', 'max' => 32],
            [['video', 'share_title', 'share_image_id', 'follow_tip'], 'string', 'max' => 64],
            [['stock_unit', 'weight_unit', 'volume_unit', 'vip_discount'], 'string', 'max' => 8],

            [['image_id'], 'safe'],

            [['name', 'cat_id'],'required'],
            [['sell_num', 'buy_num', 'show_buy_num', 'return_num', 'view_num', 'comments_num', 'price', 'costprice', 'mktprice', 'distprice', 'vip_price', 'show_stock', 'on_shelves', 'sort'], 'default', 'value' => 0],

            [['type', 'status'], 'default', 'value' => 1],

            [['check_stock'], 'default', 'value' => 1],
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
            'code' => 'Code',
            'barcode' => 'Barcode',
            'name' => 'Name',
            'subtitle' => 'Subtitle',
            'subtitle_short' => 'Subtitle Short',
            'brief' => 'Brief',
            'keywords' => 'Keywords',
            'brand_id' => 'Brand ID',
            'cat_id' => 'Cat ID',
            'spec_id' => 'Spec ID',
            'type' => 'Type',
            'image_id' => 'Image ID',
            'video' => 'Video',
            'content' => 'Content',
            'specs' => 'Specs',
            'params' => 'Params',
            'sort' => 'Sort',
            'is_hot' => 'Is Hot',
            'is_recommend' => 'Is Recommend',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
            'check_stock' => 'Check Stock',
            'show_stock' => 'Show Stock',
            'pre_sale' => 'Pre Sale',
            'on_shelves' => 'On Shelves',
            'on_shelves_at' => 'On Shelves At',
            'off_shelves_at' => 'Off Shelves At',
            'sell_num' => 'Sell Num',
            'buy_num' => 'Buy Num',
            'show_buy_num' => 'Show Buy Num',
            'return_num' => 'Return Num',
            'view_num' => 'View Num',
            'comments_num' => 'Comments Num',
            'stock_unit' => 'Stock Unit',
            'weight_unit' => 'Weight Unit',
            'volume_unit' => 'Volume Unit',
            'price' => 'Price',
            'costprice' => 'Costprice',
            'mktprice' => 'Mktprice',
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

    public function getCategory()
    {
        return $this->hasMany(GoodsCategory::className(), ['id' => 'cat_id']);
    }

    public function getProduct()
    {
        return $this->hasMany(GoodsProduct::className(), ['goods_id' => 'id']);
    }

    public function getBrand()
    {
        return $this->hasOne(Brand::className(), ['id' => 'brand_id'])->select(['id', 'name', 'logo']);
    }

    public function getFileRelation()
    {
        return $this->hasMany(FileRelation::className(), ['object_id' => 'id']);
    }

    public function getImages(){
        return $this->hasMany(File::className(),['id'=>'file_id'])->via('fileRelation');
    }

    public function getCover(){
        return $this->hasMany(File::className(),['id'=>'image_id']);
    }

    public function getVideo(){
        return $this->hasMany(File::className(),['id'=>'video']);
    }

    public function getLabels()
    {
        return $this->hasMany(Label::className(), ['id' => 'label_id'])->via('labelRelation')->select(['id', 'name', 'style', 'type']);
    }

    public function getLabelRelation()
    {
        return $this->hasMany(LabelRelation::className(), ['object_id' => 'id'])->andWhere(['type'=>LabelRelation::TYPE_GOODS]);
    }

    public function getCommentItems()
    {
        return $this->hasMany(GoodsComment::className(), ['goods_id' => 'id'])/*->onCondition(['cat_id' => 1])*/;
    }

    public function saveData($data=[])
    {
        if (!$this->checkArray($data,['imageItems','labelItems','productItems','specItems','paramItems'])) {
            return false;
        }
        $data = $this->formatData($data);

        $this->load($data);
        
        $transaction = self::getDb()->beginTransaction();
        try {

            if (!$this->save()) {
                $transaction->rollBack();
                return false;
            }

            $images = [];
            foreach ($data['imageItems'] as $k => $v) {
                $image['object_id'] = $this->id;
                $image['file_id'] = $v;
                $image['type'] = FileRelation::TYPE_GOODS;
                $image['sort'] = $k + 1;
                $images[] = $image;
            }
            FileRelation::deleteAll(['object_id'=>$this->id,'type'=>FileRelation::TYPE_GOODS]);
            self::getDb()->createCommand()
            ->batchInsert(FileRelation::tableName(),array_keys(end($images)?end($images):[]),$images)
            ->execute();

            $labels = [];
            foreach ($data['labelItems'] as $k => $v) {
                $label['object_id'] = $this->id;
                $label['label_id'] = $v;
                $label['type'] = LabelRelation::TYPE_GOODS;
                $label['sort'] = $k + 1;
                $labels[] = $label;
            }

            LabelRelation::deleteAll(['object_id'=>$this->id,'type'=>LabelRelation::TYPE_GOODS]);
            self::getDb()->createCommand()
            ->batchInsert(LabelRelation::tableName(),array_keys(end($labels)?end($labels):[]),$labels)
            ->execute();

            GoodsProduct::deleteAll(['goods_id'=>$this->id]);
            foreach ($data['productItems'] as $product) {
                $product['goods_id']    = $this->id;
                $product['on_shelves']    = $product['on_shelves']??$this->on_shelves;
                $model = new GoodsProduct();

                $model->load($product);
                $model->save();
                if ($product['imageItems']??false) {
                    $pimages = [];
                    foreach ($data['imageItems'] as $k => $v) {
                        $pimage['object_id'] = $model->id;
                        $pimage['file_id'] = $v;
                        $pimage['type'] = FileRelation::TYPE_PRODUCT;
                        $pimage['sort'] = $k + 1;
                        $pimages[] = $pimage;
                    }
                    FileRelation::deleteAll(['object_id'=>$model->id,'type'=>FileRelation::TYPE_PRODUCT]);
                    self::getDb()->createCommand()
                    ->batchInsert(FileRelation::tableName(),array_keys(end($pimages)?$pimages:[]),$pimages)
                    ->execute();
                }
            }
                // $transaction->rollBack();
            $transaction->commit();
            return true;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            Tools::breakOff($e->getMessage());
        }
    }

    public function formatData($data)
    {
        $data = parent::formatData($data);

        if ($this->isNewRecord) {
            if (isset($data['on_shelves']) && $data['on_shelves'] == 1) {
                $data['on_shelves_at']= time();
            }
        }else {
            if ($data['on_shelves'] == 1 && $this->on_shelves == 0){
                $data['on_shelves_at']= time();
            }elseif ($data['on_shelves'] == 0 && $this->on_shelves == 1) {
                $data['off_shelves_at']= time();
            }
        }
        $defProd = [];
        foreach ($data['productItems'] as $k => $item) {
            if ($item['is_default']??false) {
                $defProd = $item;
            }
            $item['name'] = $data['name'];
            $item['code'] = $item['code']??Tools::get_sn(4);
            $data['productItems'][$k] = $item;
        }

        // 设置默认单品
        if (!$defProd) {
            $defProd = $data['productItems']??0;
            $data['productItems'][0]['is_default'] = 1;
        }

        if (isset($data['specItems'])) {
            $data['specs'] = json_encode($data['specItems'],JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['paramItems'])) {
            $data['params'] = json_encode($data['paramItems'],JSON_UNESCAPED_UNICODE);
        }

        $data['price'] = $defProd['price']??0;
        $data['costprice'] = $defProd['costprice']??0;
        $data['mktprice'] = $defProd['mktprice']??0;
        $data['distprice'] = $defProd['distprice']??0;
        $data['vip_price'] = $defProd['vip_price']??0;
        $data['code'] = $defProd['code']??'';

        $data['image_id'] = $data['imageItems'][0]??'';
        return $data;
    }
}
