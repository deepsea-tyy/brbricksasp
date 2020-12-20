<?php

namespace bricksasp\models;

use Yii;
use bricksasp\spu\models\Goods;
use bricksasp\spu\models\GoodsProduct;

/**
 * This is the model class for table "{{%shopping_cart}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $product_id
 * @property int|null $num
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class ShoppingCart extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shopping_cart}}';
    }

    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'product_id', 'num', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'product_id' => 'Product ID',
            'num' => 'Num',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getProduct()
    {
        return $this->hasOne(GoodsProduct::className(),['id'=>'product_id']);
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(),['id'=>'goods_id'])->via('product')->select(['id','image_id']);
    }

    public function saveData($data)
    {
        $this->load($this->formatData($data));
        return $this->save();
    }
}
