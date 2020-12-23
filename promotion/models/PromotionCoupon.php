<?php

namespace bricksasp\promotion\models;

use Yii;
use app\models\Store;
use bricksasp\base\Tools;
use bricksasp\models\UserInfo;
use bricksasp\bill\models\Order;
use bricksasp\models\redis\Token;

/**
 * This is the model class for table "{{%promotion_coupon}}".
 * 
 *     condition_type:[
 *       { value: 1, text: '全部商品' },
 *       { value: 2, text: '商品分类' },
 *       { value: 3, text: '指定商品' },
 *       { value: 4, text: '订单满减' },
 *     ],
 *     result_type:[
 *       { value: 1, text: '商品减固定金额' },
 *       { value: 2, text: '商品折扣' },
 *       { value: 3, text: '商品一口价' },
 *       { value: 4, text: '订单减固定金额' },
 *       { value: 5, text: '订单折扣' },
 *       { value: 6, text: '订单一口价' },
 *       result_type 对应 优惠券 type
 *       
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $promotion_id
 * @property int|null $user_id
 * @property string|null $code
 * @property int|null $status 0未使用1已使用
 * @property int|null $type 1领取获得2购买获得
 * @property int|null $start_at
 * @property int|null $end_at
 * @property int|null $exclusion
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class PromotionCoupon extends \bricksasp\base\BaseActiveRecord
{
    const STATUS_NO = 0; //未使用
    const STATUS_USED = 1; //已使用
    const EXCLUSION_NO = 0; //不排他
    const EXCLUSION_YES = 1; //排他
    const TYPE_RECEIVE = 1; //领取获得
    const TYPE_BUY = 2; //购买获得


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%promotion_coupon}}';
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
            [['id', 'owner_id', 'promotion_id', 'user_id', 'status', 'type', 'start_at', 'end_at', 'exclusion', 'created_at', 'updated_at'], 'integer'],
            [['code'], 'string', 'max' => 8],
            [['status'], 'default', 'value' => 0],
            [['code'], 'default', 'value' => Yii::$app->security->generateRandomString(6)]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'owner_id' => 'Owner ID',
            'promotion_id' => 'Promotion ID',
            'user_id' => 'User ID',
            'code' => 'Code',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getPromotion()
    {
        return $this->hasOne(Promotion::className(), ['id' => 'promotion_id']);
    }

    public function getStore(){
        return $this->hasOne(Store::className(),['owner_id'=>'owner_id'])->select(['logo','name','id','owner_id','address','phone']);
    }

    public function getOrder(){
        return $this->hasOne(Order::className(),['id'=>'order_id'])->select(['id','created_at']);
    }

    public function getUserInfo(){
        return $this->hasOne(UserInfo::className(),['user_id'=>'user_id'])->select(['nickname','user_id','avatar']);
    }

    public function getCondition(){
        return $this->hasOne(PromotionCondition::className(),['promotion_id'=>'id'])->via('promotion');
    }

    /**
     * 检查优惠券是否有效
     * @param  array|int  $ids   优惠券id
     * @param  array $goods [[product_id=>goods_id]]
     * @return array
     */
    public function checkEffectiveness($ids,$owner_id)
    {
        $coupons = $this->find()->with(['condition'])->where(['id' => $ids,'owner_id'=>$owner_id])->all();
        if (!$coupons) {
            Tools::breakOff(Yii::t('messages', 40002, '优惠券'));
        }

        foreach ($coupons as $k => $item) {
            if ($item->start_at > time()) {
                Tools::breakOff(990002);
            }
            if ($item->end_at < time()) {
                Tools::breakOff(990003);
            }
            if ($item->status == self::STATUS_USED) {
                Tools::breakOff(990004);
            }
            if (count($ids) > 1 && $item->exclusion == self::EXCLUSION_YES) {
                Tools::breakOff(990005);
            }
        }
        return $coupons;
    }

    /**
     * 优惠券列表
     * @param $params
     * @param $field
     * @param $with
     * @param $status
     * @param $orderby
     * @return array|\yii\db\ActiveRecord[]
     */
    public function couponList($params,$field,$with,$status,$orderby){
        if($params['login_type'] == Token::TOKEN_TYPE_FRONTEND){
            $userWhere = ['user_id'=>$params['user_id']];
        }else{
            $userWhere = [];
        }
        if($status == PromotionCoupon::STATUS_NO){

            $where = ['>','end_at',time()];
            $data = PromotionCoupon::find()->with($with)->select($field)
                ->andWhere(['status'=>$status])
                ->andWhere($where)
                ->andFilterWhere($userWhere)
                ->orderBy($orderby)
                ->asArray()->all();
        }else{
            $data = PromotionCoupon::find()->with($with)->select($field)
                ->andFilterWhere($userWhere)
                ->andWhere(['status'=>$status])
                ->orWhere( ['and','user_id = '.$params['user_id'],'end_at < '.time()])
                ->orderBy($orderby)
                ->asArray()->all();
        }

        foreach($data as $k=>$v){
            if($v['condition']){
                $data[$k]['condition']['condition_type_name'] = PromotionCondition::TYPE_NAME[$v['condition']['condition_type']];
                $data[$k]['condition']['result_type_name'] = PromotionCondition::RESULT_TYPE_NAME[$v['condition']['result_type']];
            }
        }
        return $data;
    }
}
