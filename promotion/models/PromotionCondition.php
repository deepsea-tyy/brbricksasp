<?php

namespace bricksasp\promotion\models;

use Yii;

/**
 * This is the model class for table "{{%promotion_condition}}".
 *
 * @property int|null $promotion_id
 * @property int|null $condition_type 条件类型
 * @property string|null $condition 促销条件
 * @property string|null $content 促销内容
 * @property int|null $result_type 结果类型
 * @property string|null $result 促销结果
 */
class PromotionCondition extends \bricksasp\base\BaseActiveRecord
{
    //1全部商品2商品分类3部分商品4订单满减
    const TYPE_ALL = 1;
    const TYPE_CAT = 2;
    const TYPE_PART = 3;
    const TYPE_REDUCTION = 4;

    const RESULT_TYPE_FIX_AMOUNT = 1;
    const RESULT_TYPE_GOODS_DISCOUNT = 2;
    const RESULT_TYPE_ONE_PRICE = 3;
    const RESULT_TYPE_ORDER_FIX_AMOUNT = 4;
    const RESULT_TYPE_ORDER_DISCOUNT = 5;
    const RESULT_TYPE_ORDER_ONE_PRICE = 6;

    const TYPE_NAME = [
        self::TYPE_ALL => '全部商品',
        self::TYPE_CAT => '商品分类',
        self::TYPE_PART => '部分商品',
        self::TYPE_REDUCTION => '订单满减',
    ];

    const RESULT_TYPE_NAME = [
        self::RESULT_TYPE_FIX_AMOUNT => '商品减固定金额',
        self::RESULT_TYPE_GOODS_DISCOUNT => '商品折扣',
        self::RESULT_TYPE_ONE_PRICE => '商品一口价',
        self::RESULT_TYPE_ORDER_FIX_AMOUNT => '订单减固定金额',
        self::RESULT_TYPE_ORDER_DISCOUNT => '订单折扣',
        self::RESULT_TYPE_ORDER_ONE_PRICE => '订单一口价',
    ];



    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%promotion_condition}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['promotion_id', 'condition_type', 'result_type'], 'integer'],
            [['condition', 'content', 'result'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'promotion_id' => 'Promotion ID',
            'condition_type' => 'Condition Type',
            'condition' => 'Condition',
            'content' => 'Content',
            'result_type' => 'Result Type',
            'result' => 'Result',
        ];
    }
    
    public function getPromotion()
    {
        return $this->hasOne(Promotion::className(), ['id' => 'promotion_id']);
    }
}
