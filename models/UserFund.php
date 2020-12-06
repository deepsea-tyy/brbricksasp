<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%user_fund}}".
 *
 * @property int $user_id
 * @property float|null $amount 可提现
 * @property float|null $discount_amount 不可提现
 * @property float|null $use_amount 已消费金额
 * @property float|null $coin 代币
 * @property int|null $score 可用积分
 * @property int|null $exp 经验值
 * @property int|null $credit 信用分
 * @property int|null $version
 */
class UserFund extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_fund}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'score', 'exp', 'credit', 'version'], 'integer'],
            [['amount', 'discount_amount', 'use_amount', 'coin'], 'number'],
            [['user_id'], 'unique'],
        ];
    }

    public function behaviors()
    {
        return [
            \bricksasp\common\VersionBehavior::className()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'amount' => 'Amount',
            'discount_amount' => 'Discount Amount',
            'use_amount' => 'Use Amount',
            'coin' => 'Coin',
            'score' => 'Score',
            'exp' => 'Exp',
            'credit' => 'Credit',
            'version' => 'Version',
        ];
    }
}
