<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%runerrands_cost}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $owner_id
 * @property float|null $basic_cost 基础费
 * @property float|null $lunch_time_cost 特殊时段费
 * @property float|null $dinner_time_cost 特殊时段费
 * @property float|null $difficulty_cost 难度费5楼以上
 * @property float|null $weather_cist 天气费
 * @property int|null $platform_perc 平台抽成
 * @property int|null $stationmaster_perc 站长抽成
 * @property int|null $settlement_type 结算方式1微信零钱2银行卡
 * @property float|null $settlement_least 最低结算金额
 * @property int|null $settlement_date 结算日期
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class RunerrandsCost extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%runerrands_cost}}';
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
            [['user_id', 'owner_id', 'platform_perc', 'stationmaster_perc', 'settlement_type', 'settlement_date', 'created_at', 'updated_at'], 'integer'],
            [['basic_cost', 'lunch_time_cost', 'dinner_time_cost', 'difficulty_cost', 'weather_cist', 'settlement_least'], 'number'],
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
            'owner_id' => 'Owner ID',
            'basic_cost' => 'Basic Cost',
            'lunch_time_cost' => 'Lunch Time Cost',
            'dinner_time_cost' => 'Dinner Time Cost',
            'difficulty_cost' => 'Difficulty Cost',
            'weather_cist' => 'Weather Cist',
            'platform_perc' => 'Platform Perc',
            'stationmaster_perc' => 'Stationmaster Perc',
            'settlement_type' => 'Settlement Type',
            'settlement_least' => 'Settlement Least',
            'settlement_date' => 'Settlement Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function saveData($data)
    {
        $this->load($this->formatData($data));
        return $this->save();
    }
}
