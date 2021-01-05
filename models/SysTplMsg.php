<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\pay\Wechat;
use WeMini\Newtmpl;
use WeChat\Template;

/**
 * This is the model class for table "{{%sys_tpl_msg}}".
 *
 * @property int|null $owner_id
 * @property int|null $user_id
 * @property string|null $code 标识
 * @property string|null $wx_tpl_id 公众号模板id
 * @property string|null $wx_content 公众号模板内容
 * @property string|null $wx_mini_tpl_id 小程序模版id
 * @property string|null $wx_mini_content 小程序模板内容
 * @property string|null $wx_tpl_jump 模板消息跳转
 * @property int|null $status 1小程序2公众号3全部4关闭
 * @property int|null $type
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class SysTplMsg extends \bricksasp\base\BaseActiveRecord
{
    const PAY_SUCCESS = 'PAY_SUCCESS';
    const PAY_SUCCESS_SHARE = 'PAY_SUCCESS_SHARE';
    public static $defaultCode = [
        'PAY_SUCCESS' => [
            'title' => '付款成功提醒(通用)',
            'code' => 'PAY_SUCCESS',
            'wx_tpl_tid' => 'OPENTM207185188',
            'wx_first' => '付款成功提醒',
            'wx_mark' => '欢迎再次购买',
            'wx_tpl_jump' => [
                'url' => '',
                'miniprogram' => [
                    'appid'=>'',
                    'path' =>''
                ],
                'page' => '',
            ],
            'wx_mini_tpl_cid' => '413',
            'wx_mini_tpl_tid' => '7721',
            'wx_mini_tpl_kids'=>[1,2,3,4],
            'wx_mini_tpl_scene'=>'付款成功提醒',
        ],
        'PAY_SUCCESS_SHARE' => [
            'title' => '分销下级付款成功提醒',
            'code' => 'PAY_SUCCESS',
            'wx_tpl_tid' => 'OPENTM414564417',
            'wx_first' => '',
            'wx_mark' => '',
            'wx_mini_cid' => '',
            'wx_mini_tid' => '',
            'wx_mini_kids'=>[],
            'wx_mini_tpl_scene'=>'分销下级付款成功提醒',
        ],
    ];
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sys_tpl_msg}}';
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
            [['owner_id', 'user_id', 'status', 'type', 'created_at', 'updated_at'], 'integer'],
            [['wx_content', 'wx_mini_content', 'wx_tpl_jump'], 'string'],
            [['code'], 'string', 'max' => 32],
            [['wx_tpl_id', 'wx_mini_tpl_id'], 'string', 'max' => 64],
            [['owner_id', 'code'], 'unique', 'targetAttribute' => ['owner_id', 'code']],
            [['status', 'type'], 'default', 'value' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'owner_id' => 'Owner ID',
            'user_id' => 'User ID',
            'code' => 'Code',
            'wx_tpl_id' => 'Wx Tpl ID',
            'wx_content' => 'Wx Content',
            'wx_mini_tpl_id' => 'Wx Mini Tpl ID',
            'wx_mini_content' => 'Wx Mini Content',
            'status' => 'Status',
            'type' => 'Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function saveData($data)
    {
        $this->load($this->formatData(array_filter($data)));
        return $this->save();
    }

    /**
     * 系统消息发送
     * @param  ingteger $user_id 用户id
     * @param  array $params   模版参数
     * @param  string $scene   模版场景
     * @param  string $code    模版标识
     * @return bool
     */
    public static function send($owner_id,$user_id, $params, $scene, $code)
    {
        $tpl = static::find()->where(['owner_id'=>$owner_id, 'code'=>$code])->one();
        if (!$tpl) {
            return;
        }

        $cm = Yii::createObject([
            'class' => Wechat::className(),
            'owner_id' => $owner_id,
            'user_id' => $user_id,
            'scene' => $scene,
        ]);
        $config = $cm->config();
        $uInfo = UserInfo::find()->select(['openid'])->where(['user_id'=>$user_id, 'scene'=>$scene])->one();
        if (in_array($tpl->status, [2,3]) && ($cm->app_type == Mini::TYPE_WX_OFFICIAL || $cm->app_type == Mini::TYPE_WX_SUBSCRIBE)) { // 公众号

            preg_match_all('/\{\{([\S]{1,20})\.DATA\}\}+/',$tpl->wx_content,$pregs);
            $params = array_merge(
                [['value' => static::$defaultCode[$code]['wx_first'], 'color'=>'#173177']], 
                $params, 
                [['value' => static::$defaultCode[$code]['wx_mark'], 'color'=>'#173177']]
            );

            foreach ($pregs[1] as $k => $v) {
                $send[$v] = $params[$k];
            }
            $data = [
                'touser' => $uInfo->openid,
                'template_id' => $tpl->wx_tpl_id,
                'data' => $send,
            ];

            if ($tpl->wx_tpl_jump && $jump = json_decode($tpl->wx_tpl_jump,true)) {
                if ($jump['url']) {
                    $data['url'] = $jump['url'];
                }
                if ($jump['miniprogram']['appid'] && $jump['miniprogram']['path']) {
                    $data['miniprogram'] = $jump['miniprogram'];
                }
            }
            $model = new Template($config);
            return $model->send($data);
        }

        if (in_array($tpl->status, [1,3]) && $cm->app_type == Mini::TYPE_WX_MINI) { // 小程序
            preg_match_all('/\{\{([\S]{1,20})\.DATA\}\}+/',$tpl->wx_mini_content,$pregs);
            foreach ($pregs[1] as $k => $v) {
                $send[$v] = $params[$k];
            }
            $data = [
                'touser' => $uInfo->openid,
                'template_id' => $tpl->wx_mini_tpl_id,
                'miniprogram_state' => YII_DEBUG?'developer':'formal',
                'page' => 'index',
                'lang'=>'zh_CN',
                'data' => $send,
            ];
            $model = new Newtmpl($config);
            return $model->send($data);
        }
    }
}