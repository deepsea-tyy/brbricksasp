<?php

namespace bricksasp\spu\models;

use Yii;

/**
 * This is the model class for table "{{%goods_product}}".
 *
 * @property int $id
 * @property int|null $goods_id
 * @property string|null $name
 * @property string|null $code 商品编码
 * @property string|null $barcode 条形码
 * @property string|null $spec
 * @property int|null $on_shelves 1上架
 * @property int|null $stock 库存
 * @property int|null $freeze_stock 冻结库存
 * @property int|null $is_default 1默认展示
 * @property float|null $price 售价
 * @property float|null $costprice 成本价
 * @property float|null $mktprice 市场价
 * @property float|null $distprice 分销价
 * @property float|null $vip_price 会员价
 * @property int|null $is_vip_discount 1参与会员折扣
 * @property string|null $vip_discount 折扣0.1-10
 * @property int|null $vip 限购会员等级
 * @property float|null $weight
 * @property float|null $volume
 * @property int|null $is_delete 1正常2删除
 */
class GoodsProduct extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_product}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'goods_id', 'on_shelves', 'stock', 'freeze_stock', 'is_default', 'is_vip_discount', 'vip', 'is_delete'], 'integer'],
            [['price', 'costprice', 'mktprice', 'distprice', 'vip_price', 'weight', 'volume'], 'number'],
            [['name', 'spec'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 30],
            [['barcode'], 'string', 'max' => 64],
            [['vip_discount'], 'string', 'max' => 8],

            [['price', 'costprice', 'mktprice', 'distprice', 'vip_price', 'on_shelves', ], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => 'Goods ID',
            'name' => 'Name',
            'code' => 'Code',
            'barcode' => 'Barcode',
            'spec' => 'Spec',
            'on_shelves' => 'On Shelves',
            'stock' => 'Stock',
            'freeze_stock' => 'Freeze Stock',
            'is_default' => 'Is Default',
            'price' => 'Price',
            'costprice' => 'Costprice',
            'mktprice' => 'Mktprice',
            'distprice' => 'Distprice',
            'vip_price' => 'Vip Price',
            'is_vip_discount' => 'Is Vip Discount',
            'vip_discount' => 'Vip Discount',
            'vip' => 'Vip',
            'weight' => 'Weight',
            'volume' => 'Volume',
            'is_delete' => 'Is Delete',
        ];
    }
}
