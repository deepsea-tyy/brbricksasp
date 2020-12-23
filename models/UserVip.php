<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%user_vip}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property int|null $level
 * @property int|null $up_cdt 升级条件1订单金额/数量2指定商品
 * @property string|null $up_cdt_val
 * @property string|null $discount 折扣
 * @property int|null $duration 时间期限 月
 * @property int|null $status 1启用
 * @property int|null $is_delete
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class UserVip extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_vip}}';
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
            [['owner_id', 'user_id', 'level', 'up_cdt', 'duration', 'status', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['up_cdt_val'], 'string', 'max' => 8],
            [['discount'], 'string', 'max' => 4],
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
            'level' => 'Level',
            'up_cdt' => 'Up Cdt',
            'up_cdt_val' => 'Up Cdt Val',
            'discount' => 'Discount',
            'duration' => 'Duration',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
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