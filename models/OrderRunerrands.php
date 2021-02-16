<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;
use bricksasp\promotion\models\PromotionCoupon;
use bricksasp\promotion\models\PromotionCondition;

/**
 * This is the model class for table "{{%order_runerrands}}".
 *
 * @property int|null $order_id
 * @property string|null $content
 * @property string|null $start_place 起始地
 * @property string|null $end_place 目的地
 * @property int|null $time 办事时间
 * @property int|null $weight 重量
 * @property int|null $gender
 * @property int|null $overtime 超时 小时
 * @property float|null $tip 小费
 * @property float|null $samount 代购金额
 * @property int|null $school_id
 * @property int|null $school_area_id
 * @property string|null $phone
 */
class OrderRunerrands extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_runerrands}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'time', 'weight', 'gender', 'overtime', 'school_id', 'school_area_id'], 'integer'],
            [['content'], 'string'],
            [['tip', 'samount'], 'number'],
            [['start_place', 'end_place'], 'checkLength',],
            [['phone'], 'string', 'max' => 16],
            [['order_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'order_id' => 'Order ID',
            'content' => '服务内容',
            'start_place' => '取货地点',
            'end_place' => '服务地点',
            'time' => 'Time',
            'weight' => 'Weight',
            'gender' => '性别',
            'overtime' => '超时时间',
            'tip' => '小费',
            'samount' => '服务金额',
        ];
    }

    public function checkLength()
    {
        if (mb_strlen($this->start_place)>128 || mb_strlen($this->end_place)>128) {
            $this->addError(mb_strlen($this->start_place)>128?'start_place':'end_place','不得大于128字符。');
        }
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(),['id'=>'order_id'])->select(['id','created_at','pay_price','pay_status','complete','status']);
    }

    public function saveData($data)
    {
        if (!$this->checkArray($data,['coupon_ids'])) {
            return false;
        }
        if ($data['type'] == Order::TYPE_SCHOOL_OTHER && !Tools::is_mobile($data['phone'])) {
            $this->addError('phone','请输入正确的联系电话');
            return false;
        }
        $data = $this->formatData($data);

        $transaction = self::getDb()->beginTransaction();
        try {
            $model = new Order();
            $model->load($data);
            if ($model->save()) {
                $data['order_id'] = $model->id;
                $this->load($data);
                if (!$this->save()) {
                    $transaction->rollBack();
                    return false;
                }
                PromotionCoupon::updateAll(['status'=>PromotionCoupon::STATUS_USED],['id'=>$data['coupon_ids'],'owner_id'=>$data['owner_id']]);

                $transaction->commit();
                return $model;
            }else{
                $this->setErrors($model->errors);
                $transaction->rollBack();
            }
            return false;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            Tools::breakOff($e->getMessage());
        }
        return $this->save();
    }

    public function formatData($data)
    {
        if (!in_array($data['type']??0, [2,3,4,5])) {
            $this->addError('type','type无效');
            return false;
        }
        $data = parent::formatData($data);
        $student = StudentAuth::find()->with(['owner', 'costSetting'])->where(['user_id'=>$data['user_id']])->one();
        $cost = RunerrandsCost::find()->with(['weithtCost'])->where(['owner_id'=>$data['owner_id']])->one();
        
        if ($student->status!=1) {
            Tools::breakOff(400001);
        }

        $data['school_id'] = $student->school_id;
        $data['school_area_id'] = $student->school_area_id;
        $data['owner_id'] = $student->owner->owner_id ?? $data['owner_id'];

        $data['total_price'] = $cost->basic_cost;
        if ($student->costSetting->is_weather_cist) {//天气
            $data['total_price'] += $cost->weather_cist;
        }

        if (!empty($data['weight'])) {
            foreach ($cost['weithtCost'] as $item) {//重量
                if ($item->id == $data['weight']) {
                    $data['total_price']+=$item->price;
                    break;
                }
            }
        }
        $hour = date('H',time());
        if (($student->costSetting->is_lunch_cost??1) && $hour < 13 && $hour>=11) {//时段
            $data['total_price'] += $cost->lunch_time_cost;
        }
        if (($student->costSetting->is_dinner_cost??1) && $hour < 19 && $hour>=17) {
            $data['total_price'] += $cost->dinner_time_cost;
        }

        if (!empty($data['ship_id'])) {//楼层
            $shipAdr = ShipAddress::find()->where(['id' => $data['ship_id']])->one();
            if ($shipAdr) {
                $data['ship_area_id'] = $shipAdr->area_id;
                $data['ship_address'] = $shipAdr->address;
                $data['ship_name'] = $shipAdr->name;
                $data['ship_phone'] = $shipAdr->phone;
                $fs = ['一', '二', '三', '四'];
                $f = mb_substr($shipAdr->floor, 0,1);
                if ($f && !in_array($f, $fs)) {
                    $data['total_price'] += $cost->difficulty_cost;
                }
            }
        }

        if (!empty($data['tip'])) {
            $data['total_price'] += $data['tip'];
        }

        $data['pay_price']=$data['total_price'];
        //优惠券
        if (!empty($data['coupon_ids'])) {
            $model = new PromotionCoupon();
            $coupon =  $model->checkEffectiveness($data['coupon_ids'],$data['owner_id']);
            foreach ($coupon as $item) {
                if ($item->condition->result_type == PromotionCondition::RESULT_ORDER_FIX_REDUCE) {
                    $data['pay_price'] = $data['total_price'] - $item->condition->result;
                }
                if ($item->condition->result_type == PromotionCondition::RESULT_ORDER_DISCOUNT) {
                    $data['pay_price'] = $data['total_price'] * $item->condition->result / 10;
                }
                if ($item->condition->result_type == PromotionCondition::RESULT_ORDER_ONE_PRICE) {
                    $data['pay_price'] = $item->condition->result;
                }
                $data['coupon'][] = $item->condition;
            }
            $data['coupon'] = json_encode($data['coupon']??[]);
            $data['order_pmt'] = $data['total_price'] - $data['pay_price'];
        }

        return $data;
    }
}
