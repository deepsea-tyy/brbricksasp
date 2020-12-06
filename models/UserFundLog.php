<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%user_fund_log}}".
 *
 * @property int|null $user_id
 * @property int|null $owner_id
 * @property string|null $point
 * @property int|null $type
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
            [['user_id', 'owner_id', 'type', 'created_at'], 'integer'],
            [['point'], 'string', 'max' => 32],
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
            'type' => 'Type',
            'created_at' => 'Created At',
        ];
    }
}
