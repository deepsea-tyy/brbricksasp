<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%user_fund_log}}".
 *
 * @property int|null $user_id
 * @property int|null $owner_id
 * @property float|null $point
 * @property int|null $status 1入账2出账
 * @property int|null $type 1money2积分3信用分
 * @property int|null $object_id 来源id
 * @property int|null $object_type 1订单2提现
 * @property int|null $scene 1跑腿
 * @property float|null $amount
 * @property int|null $created_at
 */
class UserFundLog extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_fund_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'owner_id', 'status', 'type', 'object_id', 'object_type', 'scene', 'created_at'], 'integer'],
            [['point','perc'], 'number'],
            [['created_at'], 'default', 'value'=>time()],
            [['status', 'object_type','scene'], 'default', 'value'=>1],
            [['point','perc','amount'], 'default', 'value'=>0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'owner_id' => 'Owner ID',
            'point' => 'Point',
            'status' => 'Status',
            'type' => 'Type',
            'object_id' => 'Object ID',
            'object_type' => 'Object Type',
            'created_at' => 'Created At',
        ];
    }
}
