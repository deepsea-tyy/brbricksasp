<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;

/**
 * This is the model class for table "{{%setting}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $owner_id
 * @property string|null $key
 * @property string|null $val
 * @property string|null $title
 * @property string|null $val1
 * @property string|null $val2
 * @property int|null $type 分类
 * @property string|null $ext 拓展内容
 */
class Setting extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'owner_id', 'type'], 'integer'],
            [['key', 'title'], 'string', 'max' => 64],
            [['val', 'val1', 'val2'], 'string', 'max' => 255],
            [['owner_id', 'key'], 'unique', 'targetAttribute' => ['owner_id', 'key']],
            [['ext'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'owner_id' => 'Owner ID',
            'key' => 'Key',
            'val' => 'Val',
            'title' => 'title',
            'val1' => 'val1',
            'val2' => 'val2',
        ];
    }

    /**
     * 系统设置字段
     * @var [array]
     */
    public static $defaultSettings = [
        'WX_OFFICIAL_TYPE' => [
            'title' => '公众号类型',
            'val' => '',
            'option' => [ 1=>'服务和', 2=>'订阅号']
        ],
        'WX_OFFICIAL_APPID' => [
            'title' => '公众号APPID',
            'val' => '',
        ],
        'WX_OFFICIAL_SECRET' => [
            'title' => '公众号SECRET',
            'val' => '',
        ],
        'WX_OFFICIAL_ORIGINAL_ID' => [
            'title' => '公众号原始ID',
            'val' => '',
        ],
        'WX_OFFICIAL_KEY' => [
            'title' => '公众号EncodingAESKey',
            'val' => '',
        ],
        'WX_OFFICIAL_RETURN_URL' =>[
            'title' => '微信开放平台公众号回调地址',
            'val'   => '/wxweb/get-info',
        ],
        'WX_OFFICIAL_SCOPE' =>[
            'title' => '微信开放平台公众号scope',
            'val'   => 'snsapi_login',
        ],

        'WX_APPLET_APPID' => [
            'title' => '小程序APPID',
            'val' => '',
        ],
        'WX_APPLET_SECRET' => [
            'title' => '小程序SECRET',
            'val' => '',
        ],
        'WX_APPLET_ORIGINAL_ID' => [
            'title' => '小程序原始ID',
            'val' => '',
        ],
        'WX_APPLET_KEY' => [
            'title' => '小程序EncodingAESKey',
            'val' => '',
        ],

        'WX_APPLET_APPID_SHOP' => [
            'title' => '个店小程序APPID',
            'val' => '',
        ],
        'WX_APPLET_SECRET_SHOP' => [
            'title' => '个店小程序SECRET',
            'val' => '',
        ],
        'WX_APPLET_ORIGINAL_ID_SHOP' => [
            'title' => '个店小程序原始ID',
            'val' => '',
        ],
        'WX_APPLET_KEY_SHOP' => [
            'title' => '个店小程序EncodingAESKey',
            'val' => '',
        ],

        'SMS_TEN_SECRETID' => [
            'title' => '腾讯云短信SecretId',
            'val' => '',
        ],
        'SMS_TEN_SECRETKETY' => [
            'title' => '腾讯云短信SecretKey',
            'val' => '',
        ],
        'SMS_ALI_SECRETID' => [
            'title' => '阿里云短信SecretId',
            'val' => '',
        ],
        'SMS_ALI_SECRETKETY' => [
            'title' => '阿里云短信SecretKey',
            'val' => '',
        ],
        'SMS_USE_ON' => [
            'title' => '使用三方短信',
            'val'   => 'SmsTencent',
        ],
        'SMS_PLATFORM' => [
            'title' => '短信平台',
            'val'   => [
                'SmsTencent' => [
                    'title' => '腾讯云',
                    'secretid' => 'SMS_TEN_SECRETID',
                    'secretkey' => 'SMS_TEN_SECRETKETY'
                ],
                'SmsAli' => [
                    'title' => '阿里云',
                    'secretid' => 'SMS_ALI_SECRETID',
                    'secretkey' => 'SMS_ALI_SECRETKETY'
                ],
            ],
        ],



        'WX_WEB_APPID' =>[
            'title' => '微信开放平台网站应用app_id',
            'val'   => '',
        ],
        'WX_WEB_SECRET' =>[
            'title' => '微信开放平台网站应用app_secret',
            'val'   => '',
        ],
        'WX_WEB_RETURN_URL' =>[
            'title' => '微信开放平台网站应用回调地址',
            'val'   => '/wxweb/to-where',
        ],
        'WX_WEB_LOCATION_URL' =>[
            'title' => '开放平台登录后回调的地址',
            'val'   => '/static/home/#/',
        ],
        'WX_WEB_LOCATION_HOME_URL' =>[
            'title' => '官网登录后回调的地址',
            'val'   => '/static/home/#/receivecode',
        ],
        'WX_WEB_SCOPE' =>[
            'title' => '微信开放平台网站应用scope',
            'val'   => 'snsapi_login',
        ],


        'MALL_ORDER_EFFECTICE_TIME' =>[
            'title' => '订单有效时间(天)',
            'val'   => 24 * 3600,
        ],
        'MALL_ORDER_RESHIP_EFFECTICE_TIME' =>[
            'title' => '退货有效时间(天)',
            'val'   => 3 * 24 * 3600,
        ],
        'MALL_ORDER_FREE_SHIPPING' =>[
            'title' => '是否免邮',
            'val'   => 0,
            'val1'  => 100, //免邮金额
        ],

        'WEB_NAME' =>[
            'title' => '网站名称',
            'val' => '',
        ],
        'WEB_SHORT_NAME' =>[
            'title' => '网站简称',
            'val' => '',
        ],
        'WEB_FAVICON' =>[
            'title' => 'favicon',
            'val' => '',
        ],
        'WEB_LOGO' =>[
            'title' => 'logo',
            'val' => '',
        ],
        'WEB_SEO_TITLE' =>[
            'title' => 'SEO标题',
            'val' => '',
        ],
        'WEB_SEO_DESC' =>[
            'title' => 'SEO描述',
            'val' => '',
        ],
        'WEB_SEO_KEYWORDS' =>[
            'title' => 'SEO关键字',
            'val' => '',
        ],
        'WEB_SEO_BRIEF' =>[
            'title' => 'SEO简介',
            'val' => '',
        ],
        'WEB_UPLOAD_FILE_EXT' =>[
            'title' => '文件格式',
            'val' => '',
        ],
        'WEB_UPLOAD_MAX_SIZE' =>[
            'title' => '上传大小',
            'val' => '',
        ],
        'WEB_DEFAULT_ROLE' =>[
            'title' => '默认角色',
            'val' => '',
        ],
        'WEB_COIN_NAME' =>[
            'title' => '流通币名称',
            'val' => '',
        ],
        'WEB_SCORE_NAME' =>[
            'title' => '积分名称',
            'val' => '',
        ],
        'WEB_IPC' =>[
            'title' => 'ICP备',
            'val' => '',
        ],
        'WEB_PSR' =>[
            'title' => '公安备案',
            'val' => '',
        ],
        'WEB_WEBMASTER_EMAIL' =>[
            'title' => '站长邮箱',
            'val' => '',
        ],
        
        'GLOBAL_COPYRIGHT_ON' =>[
            'title' => '开启版权',
            'val' => 1,
        ],
        'GLOBAL_COPYRIGHT_DESC' =>[
            'title' => '版权说明',
            'val' => '',
        ],
        'GLOBAL_COPYRIGHT_LOGO' =>[
            'title' => '版权logo',
            'val' => '',
        ],
        'GLOBAL_VERSION' =>[
            'title' => '版本号',
            'val' => '1.0.0',
        ],
        'GLOBAL_UPDATE_TIME' =>[
            'title' => '更新时间',
            'val' => '2020-11-09',
        ],
        'GLOBAL_FILE_DOMAIN' =>[
            'title' => '文件访问域名',
            'val' => Yii::$app->request->hostInfo,
        ],

    ];

    /**
     * @OA\Schema(
     *   schema="setting",
     *   description="系统设置参数结构",
     *   @OA\Property(
     *     description="键",
     *     property="key1", 
     *     type="array", 
     *     @OA\Items(
     *       @OA\Property(description="字段名称",property="title",type="string",), 
     *       @OA\Property(description="字段值,如有option 值为option索引",property="val",type="string",),
     *       @OA\Property(description="字段值选项",property="option",type="array", @OA\Items(example="值1",)), 
     *     )
     *   ),
     * )
     */
    public static function getSetting($owner_id, $keyPrefix)
    {
        $defaultSettings = $settings = [];
        foreach (static::$defaultSettings as $key => $value) {
            if (strpos($key, $keyPrefix) !== false) {
                if (strpos($key, 'URL') !== false) {
                    $value['val'] = Yii::$app->request->hostInfo??'' . $value['val'];
                }
                $defaultSettings[$key] = $value;
            }
        }
        $res = static::find()->andWhere(['and', ['owner_id' => $owner_id], "`key` like '{$keyPrefix}%'"])->all();
        if ($res) {
            foreach ($res as $v) {
                if (strpos($v->val, '[') !== false) {
                    $v->val = json_decode($v->val);
                }
                $item = $defaultSettings[$v->key];
                $item['val'] = $v->val;
                $item['val1'] = $v->val1;
                $item['val2'] = $v->val2;
                $item['title'] = empty($v->title) ? $item['title'] : $v->title;
                $item['ext'] = empty($v->ext) ? [] : json_decode($v->ext);
                $settings[$v->key] = $item;

            }
            return array_merge($defaultSettings, $settings);
        }
        return $defaultSettings;
    }

    /**
     * 保存设置
     */
    public static function saveData($data, $owner_id, $keyPrefix)
    {
        $settings = [];
        $user = Yii::$app->getUser();
        foreach ($data as $key => $value) {
            if ($key == 'keyPrefix') {
                continue;
            }
            $row['key'] = $key;
            $row['owner_id'] = $owner_id;
            $row['user_id'] = $user->getId();
            $item = is_array($value) ? $value : json_decode($value, true);
            $item = is_array($item) ? $item : null;
            $row['val'] = is_array($item) ? $item['val'] ?? '' : $value;
            $row['title'] = $item['title'] ?? '';
            $row['val1'] = $item['val1'] ?? '';
            $row['val2'] = $item['val2'] ?? '';
            $row['ext'] = empty($item['ext']) ? '' : json_encode($item['ext']);
            $settings[] = $row;
        }
        if (empty($settings)) {
            Tools::breakOff(910001);
        }
        $transaction = static::getDb()->beginTransaction();
        try {
            static::deleteAll(['owner_id' => $owner_id, 'key' => array_keys($data)]);
        
            static::getDb()->createCommand()
            ->batchInsert(static::tableName(), array_keys(end($settings)), $settings)
            ->execute();
            $transaction->commit();
            return true;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return false;
    }

}
