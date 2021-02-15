<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;

/**
 * This is the model class for table "{{%draw_money}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property float|null $money
 * @property int|null $status 1提现成功
 * @property float|null $commission
 * @property int|null $platform 1微信2支付宝
 * @property int|null $draw_type 1零钱
 * @property int|null $scene 1跑腿
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class DrawMoney extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_draw_money}}';
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
            ['money', 'required'],
            [['id', 'owner_id', 'user_id', 'status', 'draw_type', 'platform', 'scene', 'created_at', 'updated_at'], 'integer'],
            [['money', 'commission'], 'number'],
            [['money', 'commission','status'], 'default', 'value'=>0],
            [['platform', 'draw_type','scene'], 'default', 'value'=>1],
            [['commission'], 'checkCommission'],
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
            'money' => '提现金额',
            'status' => 'Status',
            'commission' => 'Commission',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    // 手续费计算
    public function checkCommission()
    {
        
    }

    public function saveData($data)
    {   
        $fund = UserFund::find()->where(['user_id'=>$data['current_user_id']])->one();
        if ($data['money']>0 && $data['money']>$fund->amount) {
            $this->addError('money', '账户金额不足');
            return false;
        }
        $cost = RunerrandsCost::find()->where(['owner_id'=>$data['current_owner_id']])->one();

        $rate = $cost->platform_perc+$cost->stationmaster_perc;
        $perc = $data['money'] * $rate /100;

        $this->load($this->formatData($data));
        $transaction = self::getDb()->beginTransaction();
        try {
            if ($this->save() === false) {
                $transaction->rollBack();
                return false;
            }
            $log = new UserFundLog();
            $log->load([
                'owner_id'=>$this->owner_id,
                'user_id'=>$this->user_id,
                'status'=>2,
                'type'=>1,
                'point'=>$this->money,
                'object_id'=>$this->id,
                'object_type'=>2,
                'perc'=>$perc,
                'platform' => $data['platform']??null,
                'draw_type'=>$data['draw_type']??null,
                'amount' => $fund->amount - $this->money,
            ]);

            if ($log->save() === false) {
                $transaction->rollBack();
                $this->setErrors($log->errors);
                return false;
            }

            $map = [
                'and',
                ['user_id'=>$fund->user_id],
                ['>=', 'amount', $this->money]
            ];
            if (!UserFund::updateAllCounters(['amount'=>-$this->money, 'out_amount'=>$this->money],$map)) {
                $transaction->rollBack();
                Tools::breakOff('提交失败');
                return false;
            }
            $transaction->commit();
            return true;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return false;
    }
}
