<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%user_info}}".
 *
 * @property int $user_id
 * @property int|null $owner_id
 * @property int|null $show_id
 * @property string|null $avatar
 * @property string|null $name 姓名
 * @property string|null $nickname
 * @property int|null $birthday
 * @property int|null $age
 * @property int|null $gender
 * @property string|null $last_login_ip
 * @property int|null $last_login_time
 * @property string|null $last_login_area
 * @property int|null $login_count
 * @property string|null $vip 会员等级
 * @property int|null $vip_duration
 * @property int|null $platform 用户类型 1站内用户2微信用户3支付宝用户4抖音用户
 * @property string|null $open_id
 * @property string|null $country
 * @property string|null $province
 * @property string|null $city
 * @property string|null $country_code
 * @property string|null $unionid
 * @property int|null $level 级别
 * @property int|null $company_id 公司id
 * @property string|null $uuid
 * @property string|null $mark 备注
 * @property int|null $type 注册入口 1普通会员2商家
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class UserInfo extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_info}}';
    }

    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
        ];
    }

    public static function primaryKey(){
        return ['user_id'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'owner_id', 'show_id', 'birthday', 'age', 'gender', 'last_login_time', 'login_count', 'vip_duration', 'platform', 'level', 'company_id', 'type', 'created_at', 'updated_at'], 'integer'],
            [['avatar'], 'string', 'max' => 255],
            [['name', 'nickname', 'vip', 'country_code'], 'string', 'max' => 32],
            [['last_login_ip', 'last_login_area', 'mark'], 'string', 'max' => 64],
            [['open_id', 'country', 'province', 'city', 'unionid', 'uuid'], 'string', 'max' => 128],
            [['user_id'], 'unique'],
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
            'show_id' => 'Show ID',
            'avatar' => 'Avatar',
            'name' => 'Name',
            'nickname' => 'Nickname',
            'birthday' => 'Birthday',
            'age' => 'Age',
            'gender' => 'Gender',
            'last_login_ip' => 'Last Login Ip',
            'last_login_time' => 'Last Login Time',
            'last_login_area' => 'Last Login Area',
            'login_count' => 'Login Count',
            'vip' => 'Vip',
            'vip_duration' => 'Vip Duration',
            'platform' => 'Platform',
            'open_id' => 'Open ID',
            'country' => 'Country',
            'province' => 'Province',
            'city' => 'City',
            'country_code' => 'Country Code',
            'unionid' => 'Unionid',
            'level' => 'Level',
            'company_id' => 'Company ID',
            'uuid' => 'Uuid',
            'mark' => 'Mark',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
