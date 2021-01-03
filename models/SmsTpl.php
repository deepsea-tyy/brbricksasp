<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%sms_tpl}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property string|null $code 模版标识
 * @property string|null $content
 * @property int|null $platform 1腾讯2阿里
 * @property string|null $sign 签名
 * @property int|null $tpl_id 平台模版id号
 * @property int|null $is_delete
 * @property string|null $appid
 */
class SmsTpl extends \bricksasp\base\BaseActiveRecord
{
    public static $defaultCode = [
        [
            'code' => 'TPL_VCODE_PATTERN',
            'title' => '通用短信验证码模版',
            'content' =>'你的验证码为{1}，请于{2}分钟内填写，如非本人操作，请忽略本短信。',
        ],
        [
            'code' => 'TPL_VCODE_LOGIN',
            'title' => '登录短信验证码模版',
            'content' => '{1}为您的登录验证码，请于{2}分钟内填写，如非本人操作，请忽略本短信。',
        ],
        [
            'code' => 'TPL_VCODE_REGISTER',
            'title' => '注册短信验证码模版',
            'content' => '您正在申请手机注册，验证码为：{1}，{2}分钟内有效！',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sms_tpl}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['platform', 'required'],
            [['owner_id', 'user_id', 'is_delete'], 'integer'],
            [['code', 'appid'], 'string', 'max' => 32],
            [['content'], 'string', 'max' => 255],
            [['tpl_id', 'sign',], 'string', 'max' => 16],
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
            'code' => 'Code',
            'content' => 'Content',
            'platform' => 'Platform',
            'sign' => 'Sign',
            'appid' => 'appid',
        ];
    }

    public function saveData($data)
    {
        $this->load($this->formatData($data));
        return $this->save();
    }
}
