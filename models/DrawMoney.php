<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%draw_money}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property float|null $money
 * @property int|null $status 1提现成功
 * @property float|null $commission
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
        return '{{%draw_money}}';
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
            [['id', 'owner_id', 'user_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['money', 'commission'], 'number'],
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
            'money' => 'Money',
            'status' => 'Status',
            'commission' => 'Commission',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
