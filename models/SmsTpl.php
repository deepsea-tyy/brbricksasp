<?php

namespace bricksasp\models;

use Yii;

/**
 * This is the model class for table "{{%sms_template}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property string|null $code 模版标识
 * @property string|null $content
 * @property int|null $platform 1腾讯2阿里
 * @property string|null $sign 签名
 * @property int|null $tpl_id 平台模版id号
 * @property string|null $appid
 */
class SmsTpl extends \bricksasp\base\BaseActiveRecord
{
    public static $defaultCode = [
        'TPL_VCODE_PATTERN' => [
            'title' => '通用短信验证码模版',
            'content' => [
                'title' => '模版事例',
                'val' => '你的验证码为{1}，请于{2}分钟内填写，如非本人操作，请忽略本短信。',
            ],
            'appid'=>['title' => 'appid','val' => ''],
            'tpl_id' => ['title' => '模版id','val' => '',],
            'appid'=>['title' => 'appid','val' => ''],
            'sign' => ['title' => '签名','val' => '',],
            'appid'=>['title' => 'appid','val' => ''],
        ],
        'TPL_VCODE_LOGIN' => [
            'title' => '登录短信验证码模版',
            'content' => [
                'title' => '模版事例',
                'val' => '{1}为您的登录验证码，请于{2}分钟内填写，如非本人操作，请忽略本短信。',
            ],
            'appid'=>['title' => 'appid','val' => ''],
            'tpl_id' => ['title' => '模版id','val' => '',],
            'appid'=>['title' => 'appid','val' => ''],
            'sign' => ['title' => '签名','val' => '',],
            'appid'=>['title' => 'appid','val' => ''],
        ],
        'TPL_VCODE_REGISTER' => [
            'title' => '注册短信验证码模版',
            'content' => [
                'title' => '模版事例',
                'val' => '您正在申请手机注册，验证码为：{1}，{2}分钟内有效！',
            ],
            'appid'=>['title' => 'appid','val' => ''],
            'tpl_id' => ['title' => '模版id','val' => '',],
            'appid'=>['title' => 'appid','val' => ''],
            'sign' => ['title' => '签名','val' => '',],
            'appid'=>['title' => 'appid','val' => ''],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sms_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['platform', 'required'],
            [['owner_id', 'user_id', 'tpl_id'], 'integer'],
            [['code'], 'string', 'max' => 64],
            [['content'], 'string', 'max' => 255],
            [['sign'], 'string', 'max' => 16],
            [['appid'], 'string', 'max' => 32],
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

    public static function tpls($owner_id, $platform='')
    {
        $defaultCode = [];
        $models = static::find()->where(['platform' => $platform, 'owner_id'=>$owner_id])->all();
        foreach ($models as $item) {
            foreach (static::$defaultCode[$item->code] as $k => $v) {
                if (!empty($item->$k)) {
                    $v['val'] = $item->$k;
                }
                $defaultCode[$item->code][$k] = $v;
            }
        }
        return array_merge(static::$defaultCode,$defaultCode);
    }
}
