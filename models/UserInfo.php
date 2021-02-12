<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\File;

/**
 * This is the model class for table "{{%user_info}}".
 *
 * @property int $id
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
 * @property string|null $openid
 * @property string|null $unionid
 * @property int|null $level 级别
 * @property int|null $company_id 公司id
 * @property string|null $uuid
 * @property string|null $mark 备注
 * @property int|null $type 注册入口 1普通会员2商家
 * @property int|null $scene 应用场景
 * @property int|null $school_id
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'owner_id', 'show_id', 'birthday', 'age', 'gender', 'last_login_time', 'login_count', 'vip_duration', 'platform', 'level', 'company_id', 'type', 'school_id', 'created_at', 'updated_at'], 'integer'],
            [['avatar'], 'string', 'max' => 255],
            [['name', 'nickname', 'vip'], 'string', 'max' => 32],
            [['last_login_ip', 'last_login_area', 'mark'], 'string', 'max' => 64],
            [['openid', 'unionid', 'uuid'], 'string', 'max' => 128],
            [['scene', 'type'], 'default', 'value' => Mini::SCENE_WX_DEFAULT],
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
            'openid' => 'Open ID',
            'unionid' => 'Unionid',
            'level' => 'Level',
            'company_id' => 'Company ID',
            'uuid' => 'Uuid',
            'mark' => 'Mark',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }

    public function getFile()
    {
        return $this->hasOne(File::className(),['id'=>'avatar']);
    }

    public function getStudentAuth()
    {
        return $this->hasOne(StudentAuth::className(),['user_id'=>'user_id']);
    }

    public function saveData($data)
    {
        $this->load($this->formatData($data));
        if (!empty($data['avatarUrl'])) {
            $file_id = Tools::get_sn(10);
            Tools::download_file($data['avatarUrl'],$file_id . '.jpg', Yii::$app->basePath . '/web/file/avatar');
            $img = getimagesize(Yii::$app->basePath . '/web/file/avatar/' . $file_id . '.jpg');
            $model = new File();
            $model->load([
                'id' => $file_id,
                'name' => 'wx_avatar'.$this->user_id,
                'mime' => 'image/jpeg',
                'ext' => 'jpg',
                'file_url' => '/file/avatar/' . $file_id . '.jpg',
                'photo_width' => empty($img[0]) ? 0 : $img[0],
                'photo_hight' => empty($img[1]) ? 0 : $img[1],
                'user_id' => $this->user_id,
                'owner_id' => $this->owner_id,
            ]);
            $model->save();
            if ($this->avatar) {
                File::remove($this->avatar);
            }
            $this->avatar = $model->id;
        }
        return $this->save();
    }
}
