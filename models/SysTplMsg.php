<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;

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
 * @property int|null $scene 1默认官方官网 2校园跑腿用户端 3校园跑腿骑手端 4其他
 * @property int|null $status 0关闭1小程序2公众号3全部
 * @property int|null $type
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class SysTplMsg extends \bricksasp\base\BaseActiveRecord
{
    public static $defaultCode = [
        'PAY_SUCCESS' => [
            'title' => '付款成功提醒(通用)',
            'code' => 'PAY_SUCCESS',
            'wx_tpl_no' => 'OPENTM207185188',
            'wx_first' => '',
            'wx_mark' => '',
            'wx_mini_tpl_no' => '7490',
            'wx_mini_tpl_kids'=>[2,4],
            'wx_mini_tpl_scene'=>'付款成功提醒',
        ],
        'PAY_SUCCESS_SHARE' => [
            'title' => '分销下级付款成功提醒',
            'code' => 'PAY_SUCCESS',
            'wx_tpl_no' => 'OPENTM414564417',
            'wx_first' => '',
            'wx_mark' => '',
            'wx_mini_tpl_no' => '',
            'wx_mini_tpl_kids'=>[],
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
            [['owner_id', 'user_id', 'scene', 'status', 'type', 'created_at', 'updated_at'], 'integer'],
            [['wx_content', 'wx_mini_content'], 'string'],
            [['code'], 'string', 'max' => 32],
            [['wx_tpl_id', 'wx_mini_tpl_id'], 'string', 'max' => 64],
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
            'scene' => 'Scene',
            'status' => 'Status',
            'type' => 'Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function saveData($data)
    {
        $this->load($this->formatData($data));
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
    public static function send($user_id, $params, $scene, $code)
    {
        $uinfo = UserInfo::find()->select(['owner_id'])->where(['user_id'=>$user_id])->one();

        $tpl = static::find()->where(['scene'=>$scene, 'code'=>$code])->one();
        if (!$tpl) {
            return;
        }
        if ($tpl->open_gzh && $conn->type == UserConnect::TYPE_GZH) {//发送公众号模版

            preg_match_all('/\{\{([\S]{1,20})\.DATA\}\}+/',$tpl->wx_content,$pregs);
            $params = array_merge([['value' => $tpl->wx_first?$tpl->wx_first:static::$defaultCode[$code]['first']]], $params, [['value' => $tpl->wx_mark?$tpl->wx_mark:static::$defaultCode[$code]['mark']]]);

            foreach ($pregs[1] as $k => $v) {
                $send[$v] = $params[$k];
            }

            $data = [
                'touser' => $conn->openid,
                'template_id' => $tpl->wx_tpl_id,
                'url' => $tpl->wx_tpl_url ? $tpl->wx_tpl_url:'',
                'miniprogram' => [
                    'appid' => $tpl->wx_tpl_lite_appid,
                    'path' => $tpl->wx_tpl_lite_path,
                ],
                'data' => $send,
            ];
            // print_r($data);exit();
            $model = new Template($config);
            $resGzh = $model->send($data);
            return $resGzh;
        }

        if ($tpl->open_lite && ($conn->type == UserConnect::TYPE_MAIL || $conn->type == UserConnect::TYPE_STORE)) {//发送小程序模版
            preg_match_all('/\{\{([\S]{1,20})\.DATA\}\}+/',$tpl->wx_lite_content,$pregs);
            foreach ($pregs[1] as $k => $v) {
                $send[$v] = $params[$k];
            }
            $data = [
                'touser' => $conn->openid,
                'template_id' => $tpl->wx_lite_tpl_id,
                'miniprogram_state' => 'developer',
                'page' => 'developer',
                'lang'=>'zh_CN',
                'data' => $send,
            ];
            // print_r($data);exit();
            $model = new Newtmpl($config);
            $resGzh = $model->send($data);
            return $resGzh;
        }

        if ($resSms || $resGzh || $resLite || $resWebsite) {
            return true;
        }
        return fale;
    }
}