<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;
use bricksasp\promotion\models\PromotionCoupon;

/**
 * This is the model class for table "{{%order_runerrands}}".
 *
 * @property int|null $order_id
 * @property string|null $content
 * @property string|null $start_place 起始地
 * @property string|null $end_place 目的地
 * @property string|null $time 办事时间
 * @property int|null $weight 重量
 * @property int|null $gender
 * @property int|null $overtime 超时 小时
 * @property float|null $tip 小费
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
            [['order_id', 'weight', 'gender', 'overtime'], 'integer'],
            [['content'], 'string'],
            [['tip'], 'number'],
            [['start_place', 'end_place'], 'string', 'max' => 128],
            [['time'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'order_id' => 'Order ID',
            'content' => 'Content',
            'start_place' => 'Start Place',
            'end_place' => 'End Place',
            'time' => 'Time',
            'weight' => 'Weight',
            'gender' => 'Gender',
            'overtime' => 'Overtime',
            'tip' => 'Tip',
        ];
    }

    public function saveData($data)
    {
        if (!$this->checkArray($data,['coupon_ids'])) {
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

                $transaction->commit();
                return true;
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
        $student = StudentAuth::find()->select(['school_id','school_area_id'])->where(['user_id'=>$data['user_id']])->one();
        $schoolRel = StoreRelation::find()->where(['type'=>StoreRelation::TYPE_SCHOOL, 'object_id'=>$student->school_area_id??$student->school_id])->one();
        if ($schoolRel) {
            $data['owner_id'] = $schoolRel->owner_id;
        }

        $cost = RunerrandsCost::find()->with(['weithtCost'])->where(['owner_id'=>$data['owner_id']])->one();
        $setting = Setting::getSetting($data['owner_id'],'RUNERRANDS');//RUNERRANDS_WEATHER_ON

        $data['total_price'] = $cost->basic_cost;
        if ($setting['RUNERRANDS_WEATHER_ON']['val']??0) {//天气
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
        if ($hour < 13 && $hour>=11) {//时段
            $data['total_price'] += $cost->lunch_time_cost;
        }
        if ($hour < 19 && $hour>=17) {
            $data['total_price'] += $cost->dinner_time_cost;
        }

        if (!empty($data['ship_id'])) {//楼层
            $shipAdr = ShipAddress::find()->where(['id' => $data['ship_id']])->one();
            if ($shipAdr) {
                $data['ship_area_id'] = $shipAdr->area_id;
                $data['ship_address'] = $shipAdr->address;
                $data['ship_name'] = $shipAdr->name;
                $data['ship_phone'] = $shipAdr->phone;
                if ($shipAdr->floor >=5) {
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
                if ($item->result_type == PromotionCondition::RESULT_FIX_REDUCE) {
                    $data['pay_price'] = $data['total_price'] - $item->result;
                }
                if ($item->result_type == PromotionCondition::RESULT_DISCOUNT) {
                    $data['pay_price'] = $data['total_price'] * $item->result / 10;
                }
                if ($item->result_type == PromotionCondition::RESULT_ONE_PRICE) {
                    $data['pay_price'] = $item->result;
                }
            }
            PromotionCoupon::updateAll(['status'=>PromotionCoupon::STATUS_USED],['id'=>$data['coupon_ids'],'owner_id'=>$data['owner_id']]);
        }

        return $data;
    }
}
