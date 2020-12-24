<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%mini}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property int|null $parent_id 微信小程序对应的公众号
 * @property int|null $platform 1微信2支付宝3抖音
 * @property string|null $appid
 * @property string|null $app_secret 开发密钥
 * @property string|null $app_original_id 原始id
 * @property string|null $encoding_key 消息加密密钥
 * @property int|null $type 微信1小程序2服务号3订阅号
 * @property string|null $avatar
 * @property string|null $name
 * @property int|null $status 1启用
 * @property int|null $is_delete
 * @property int|null $scene 场景1默认
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Mini extends \bricksasp\base\BaseActiveRecord
{
    const SCENE_WX_DEFAULT = 1; // 小程序
    const SCENE_WX_OFFICIAL = 2; // 公众号

    const PLATFORM_WX = 1; // 微信
    const PLATFORM_ALI = 2; // 支付宝
    const PLATFORM_DY = 3; // 抖音

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mini}}';
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
            [['owner_id', 'user_id', 'parent_id', 'platform', 'type', 'status', 'is_delete', 'scene', 'created_at', 'updated_at'], 'integer'],
            [['appid', 'app_secret', 'encoding_key', 'avatar'], 'string', 'max' => 64],
            [['app_original_id'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 16],
            [['scene'], 'default', 'value' => self::SCENE_WX_DEFAULT],
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
            'platform' => 'Platform',
            'appid' => 'Appid',
            'app_secret' => 'App Secret',
            'app_original_id' => 'App Original ID',
            'encoding_key' => 'Encoding Key',
            'type' => 'Type',
            'avatar' => 'Avatar',
            'name' => 'Name',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
            'scene' => 'Scene',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getFile()
    {
        return $this->hasOne(File::className(), ['id' => 'avatar'])->select(['id', 'file_url']);
    }

    public function saveData($data)
    {
        $this->load($this->formatData($data));
        return $this->save();
    }
}
