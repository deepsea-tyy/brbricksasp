<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property string|null $email
 * @property int|null $mobile
 * @property int $status
 * @property string|null $access_token
 * @property int $type 1会员账号2管理员账号
 * @property string|null $invite_code
 * @property int $created_at
 * @property int $updated_at
 */
class User extends \bricksasp\base\BaseActiveRecord
{
    const TYPE_DEFAULT = 1; //普通会员

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
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
            [['username', 'auth_key', 'password_hash',], 'required'],
            [['mobile', 'status', 'is_delete', 'created_at', 'updated_at'], 'integer'],
            [['username', 'password_hash', 'password_reset_token'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['email', 'access_token'], 'string', 'max' => 64],
            [['invite_code'], 'string', 'max' => 8],
            [['username'], 'unique'],
            [['password_reset_token'], 'unique'],
            [['email'], 'unique'],
            [['mobile'], 'unique'],
            [['type'], 'default', 'value'=>self::TYPE_DEFAULT],
            [['invite_code'], 'default', 'value'=>Tools::random_str()],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'status' => 'Status',
            'access_token' => 'Access Token',
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
