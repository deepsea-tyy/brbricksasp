<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%sms_setting}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property string|null $secret_id
 * @property string|null $secret_key
 * @property int|null $platform
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class SmsSetting extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sms_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['platform', 'required'],
            [['owner_id', 'user_id', 'platform', 'created_at', 'updated_at'], 'integer'],
            [['secret_id', 'secret_key'], 'string', 'max' => 64],
            ['platform', 'in', 'range'=> [1,2]],
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
            'secret_id' => 'Secret ID',
            'secret_key' => 'Secret Key',
            'platform' => 'Platform',
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
