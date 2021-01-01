<?php

namespace bricksasp\models;

use Yii;

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
        list($data, $orderItems) = $this->formatData($data);

        $transaction = self::getDb()->beginTransaction();
        try {
            $model = new Order();
            $model->load($data);
            if ($model->save()) {
                $orderItems['order_id'] = $model->id;
                $this->load($data);
                $this->save();
                if (!$this->id) {
                    $transaction->rollBack();
                    Tools::breakOff('下单失败,请重试');
                }

                $transaction->rollBack();
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
        $data = parent::formatData($data);
        $student = StudentAuth::find()->select(['school_id','school_area_id'])->where(['user_id'=>$data['user_id']])->one();
        $schoolRel = StoreRelation::find()->where(['type'=>StoreRelation::TYPE_SCHOOL, 'object_id'=>$student->school_area_id??$student->school_id])->one();
        $cost = RunerrandsCost::find()->where(['owner_id'=>1])->one();
        $setting = Setting::getSetting($schoolRel->owner_id,'RUNERRANDS');//RUNERRANDS_WEATHER_ON
        $data['total_price']=0;
        $data['pay_price']=0;
        $data['pay_platform']=0;
        print_r($data);exit;

        return $data;
    }
}
