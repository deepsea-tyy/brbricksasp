<?php

namespace bricksasp\promotion\models;

use Yii;
use bricksasp\base\Tools;
use bricksasp\spu\models\Goods;

/**
 * This is the model class for table "{{%promotion}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $name
 * @property int|null $num 促销数量
 * @property int|null $receive_num 限领次数
 * @property int|null $participant_num 参与人数
 * @property string|null $instruction 使用说明
 * @property int|null $scene 使用场景 1默认
 * @property int|null $type 1优惠券2促销3团购4秒杀
 * @property string|null $code 调用代码
 * @property int|null $start_at
 * @property int|null $end_at
 * @property int|null $exclusion 排他1是2否
 * @property int|null $sort 排序/权重
 * @property int|null $status 1显示
 * @property int|null $is_delete
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Promotion extends \bricksasp\base\BaseActiveRecord
{
    const TYPE_COUPON = 1;  //优惠券
    const TYPE_PROM = 2;  //促销
    const TYPE_GROUP = 3; //团购
    const TYPE_SECKILL = 4; //秒杀

    //状态
    const STATUS_NO = 0; //不显示
    const STATUS_YES = 1; //正常 显示
    const STATUS_WAIT = 2; //待审核
    const STATUS_REFUSE = 3; //审核拒绝
    const STATUS_STOP = 4; //暂停领券
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%promotion}}';
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
            [['name'], 'required'],
            [['user_id', 'owner_id', 'num', 'receive_num', 'participant_num', 'scene', 'type', 'start_at', 'end_at', 'exclusion', 'sort', 'status', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['instruction'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 16],
            [['receive_num', 'scene', 'type', 'sort', 'exclusion', 'status'], 'default', 'value' => 1],
            [['participant_num'], 'default', 'value' => 0],
            [['code'], 'default', 'value' => Yii::$app->security->generateRandomString(6)]
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
            'name' => 'Name',
            'num' => 'Num',
            'receive_num' => 'Receive Num',
            'participant_num' => 'Participant Num',
            'instruction' => 'Instruction',
            'scene' => 'Scene',
            'type' => 'Type',
            'code' => 'Code',
            'start_at' => 'Start At',
            'end_at' => 'End At',
            'exclusion' => 'Exclusion',
            'sort' => 'Sort',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getCondition()
    {
        return $this->hasOne(PromotionCondition::className(), ['promotion_id' => 'id'])->asArray();
    }

    public function getPromotionCoupon(){
        return $this->hasMany(PromotionCoupon::className(),['promotion_id'=>'id']);
    }

    public function saveData($data)
    {
        if (!$this->checkArray($data,['condition'])) {
            return false;
        }

        $this->load($this->formatData($data));

        try {
            $transaction = self::getDb()->beginTransaction();

            if (!$this->save()) {
                $transaction->rollBack();
                return false;
            }
            if (isset($data['condition'])) {
                $data['condition']['promotion_id'] = $this->id;
                PromotionCondition::deleteAll(['promotion_id'=>$this->id]);
                $condition = new PromotionCondition();
                $condition->load($data['condition']);
                if (!$condition->save()) {
                    $transaction->rollBack();
                    foreach ($condition->errors as $k => $v) {
                        $this->addError($k,$v[0]);
                    }
                    return false;
                }
            }

            $transaction->commit();
            return true;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            Tools::breakOff($e->getMessage());
        }
    }

    /**
     * 领券
     * @param $data
     * @return bool|PromotionCoupon
     * @throws \yii\web\HttpException
     */
    public function receiveCoupon($data)
    {
        $promotion = self::findOne($data['promotion_id']??0);
        if (!$promotion) {
            Tools::breakOff(40001);
        }
        $userCouponCount = PromotionCoupon::find()->where(['promotion_id' => $data['promotion_id'], 'user_id' => $data['current_user_id']])->count();

        if($promotion->status != static::STATUS_YES){
            Tools::breakOff('优惠券不可领取');
        }
        if ($userCouponCount >= $promotion->receive_num) {
            Tools::breakOff(990001);
        }

        if ($promotion->participant_num >= $promotion->num) {
            Tools::breakOff('优惠券已领完');
        }

        $condition = PromotionCondition::find()->where(['promotion_id' => $data['promotion_id']])->one();
        $cdata['type']      = $condition->result_type;
        $cdata['content']   = $condition->result;
        $cdata['start_at']  = $promotion->start_at;
        $cdata['end_at']    = $promotion->end_at;
        $cdata['exclusion'] = $promotion->exclusion;
        $cdata['owner_id']  = $promotion->owner_id;
        $cdata['user_id']   = $data['current_user_id'];
        $cdata['promotion_id']  = $promotion->id;
        $model = new PromotionCoupon();

        $transaction = Promotion::getDb()->beginTransaction();
        try{
            $promotion->participant_num += 1;
            $model->load($cdata);
            if(!$promotion->save() || !$model->save()){
                $transaction->rollBack();
                return false;
            }
            $transaction->commit();
        }catch (\Exception $e){
            $transaction->rollBack();
            return false;
        }

        return $model;
    }


    /**
     * 获取优惠券和商品的展示信息
     * @param $data
     * @return array
     */
    public function getPromotionList($data){
        $promotion = self::find()
            ->where(['owner_id'=>$data['owner_id']])
            ->andWhere(['<','start_at',time()])
            ->andWhere(['>','end_at',time()])
            ->with('condition')
            ->asArray()->all();
        ;
        $couponList = [];
        $otherList = [];
        if($promotion){
            $already = [];
            $userCoupon = PromotionCoupon::find()->where(['owner_id'=>$data['owner_id']])->select(['COUNT(*) as total','promotion_id'])->groupBy('promotion_id')->asArray()->all();
            if($userCoupon){
                $already = array_column($userCoupon,'promotion_id');
            }
            foreach($promotion as $k=>$v){
                if($v['type'] == Promotion::TYPE_COUPON){
                    if($v['condition']){
                        $v['condition']['condition_type_name'] = PromotionCondition::TYPE_NAME[$v['condition']['condition_type']];
                        $v['condition']['result_type_name'] = PromotionCondition::RESULT_TYPE_NAME[$v['condition']['result_type']];
                    }

                    $v['already'] = 0;
                    if(in_array($v['id'],$already)){
                        $v['already'] = 1;
                    }
                    $couponList[] = $v;

                }else{
                    $where['owner_id'] = $data['owner_id'];
                    switch ($v['condition']['condition_type']){
                        case PromotionCondition::TYPE_ALL||PromotionCondition::TYPE_REDUCTION:
                            break;
                        case PromotionCondition::TYPE_CAT:
                            $where['cat_id'] = $v['condition']['content'];
                            break;
                        case PromotionCondition::TYPE_PART:
                            $where['id'] = explode(',',$v['condition']['content']);
                            break;
                    }
                    $goods = Goods::find()->select(['id','name','subtitle','brief','price','image','sell_count'])->where($where)->asArray()->all();
                    if($goods){
                        foreach($goods as $key=>$val){
                            $val['result_type'] = $v['condition']['result_type'];
                            $val['result'] = $v['condition']['result'];
                            $otherList[] = $val;
                        }
                    }

                }
            }
        }

        return ['couponList'=>$couponList,'otherList'=>$otherList];
    }


}
