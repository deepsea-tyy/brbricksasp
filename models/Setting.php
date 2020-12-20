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
 * @property string|null $title
 * @property string|null $key
 * @property string|null $val
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
            [['ext'], 'string'],
            [['title', 'key'], 'string', 'max' => 64],
            [['val', 'val1', 'val2'], 'string', 'max' => 255],
            [['owner_id', 'key'], 'unique', 'targetAttribute' => ['owner_id', 'key']],
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
            'title' => 'Title',
            'key' => 'Key',
            'val' => 'Val',
            'val1' => 'Val1',
            'val2' => 'Val2',
            'type' => 'Type',
            'ext' => 'Ext',
        ];
    }

    /**
     * 系统设置字段
     * @var [array]
     */
    public static $defaultSettings = [

        'WX_OPEN_APPID' =>[
            'title' => '微信开放平台网站应用app_id',
            'val'   => '',
        ],
        'WX_OPEN_SECRET' =>[
            'title' => '微信开放平台网站应用app_secret',
            'val'   => '',
        ],
        'WX_OPEN_RETURN_URL' =>[
            'title' => '微信开放平台网站应用回调地址',
            'val'   => '/wxweb/to-where',
        ],
        'WX_OPEN_LOCATION_URL' =>[
            'title' => '开放平台登录后回调的地址',
            'val'   => '/static/home/#/',
        ],
        'WX_OPEN_LOCATION_HOME_URL' =>[
            'title' => '官网登录后回调的地址',
            'val'   => '/static/home/#/receivecode',
        ],
        'WX_OPEN_SCOPE' =>[
            'title' => '微信开放平台网站应用scope',
            'val'   => 'snsapi_login',
        ],


        'ORDER_DURATION' =>[
            'title' => '订单有效时间(天)',
            'val'   => 2,
        ],
        'RETURN_DURATION' =>[
            'title' => '退货有效时间(天)',
            'val'   => 7,
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
        
        'COPYRIGHT_ON' =>[
            'title' => '开启版权',
            'val' => 1,
        ],
        'COPYRIGHT_DESC' =>[
            'title' => '版权说明',
            'val' => '',
        ],
        'COPYRIGHT_LOGO' =>[
            'title' => '版权logo',
            'val' => '',
        ],
        'VERSION' =>[
            'title' => '版本号',
            'val' => '1.0.0',
        ],
        'UPDATE_TIME' =>[
            'title' => '更新时间',
            'val' => '2020-11-09',
        ],
        'FILE_DOMAIN' =>[
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
