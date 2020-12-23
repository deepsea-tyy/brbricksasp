<?php

namespace bricksasp\promotion\models;

use Yii;

/**
 * This is the model class for table "{{%promotion_condition}}".
 *
 * @property int|null $promotion_id
 * @property int|null $condition_type 达成条件类型 1最少金额2最少数量
 * @property string|null $condition 达成条件
 * @property int|null $content_type 促销类型 1商品分类2指定商品3指定单品4订单满减
 * @property string|null $content 促销内容
 * @property int|null $result_type 促销结果 1商品减固定金额2商品折扣3商品一口价4订单减固定金额5订单折扣6订单一口价
 * @property string|null $result 促销结果
 */
class PromotionCondition extends \bricksasp\base\BaseActiveRecord
{
    const CONDITION_MONEY = 1;
    const CONDITION_NUM = 2;
    
    const CONTENT_CAT = 1;
    const CONTENT_GOODS = 2;
    const CONTENT_PRODUCT = 3;
    const CONTENT_ACHIEVE = 4;

    const RESULT_FIX_REDUCE = 1;
    const RESULT_DISCOUNT = 2;
    const RESULT_ONE_PRICE = 3;
    const RESULT_ORDER_FIX_REDUCE = 4;
    const RESULT_ORDER_DISCOUNT = 5;
    const RESULT_ORDER_ONE_PRICE = 6;

    const CONDITION_NAME = [
        self::CONTENT_CAT => '商品分类',
        self::CONTENT_GOODS => '指定商品',
        self::CONTENT_PRODUCT => '指定单品',
        self::CONTENT_ACHIEVE => '订单满减',
    ];

    const RESULT_NAME = [
        self::RESULT_FIX_REDUCE => '商品减固定金额',
        self::RESULT_DISCOUNT => '商品折扣',
        self::RESULT_ONE_PRICE => '商品一口价',
        self::RESULT_ORDER_FIX_REDUCE => '订单减固定金额',
        self::RESULT_ORDER_DISCOUNT => '订单折扣',
        self::RESULT_ORDER_ONE_PRICE => '订单一口价',
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
            [['promotion_id', 'condition_type', 'content_type', 'result_type'], 'integer'],
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
            'content_type' => 'Content Type',
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
