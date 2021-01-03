<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;
use bricksasp\rbac\models\User;

/**
 * This is the model class for table "{{%sms}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $mobile
 * @property int|null $status 使用状态0未发送1已发送2已使用
 * @property string|null $message
 * @property int|null $type 1验证码2其他信息
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Sms extends \bricksasp\base\BaseActiveRecord
{
    const TYPE_VCODE = 1;//验证码
    const TYPE_CONTENT = 2;//短信消息
    const STATUS_NO_SEND = 0; //未发送
    const STATUS_SEND = 1; //已发送
    const STATUS_USED = 2; //已使用
    const SMS_DURATION = 6000; //短信过期时间
    const SMS_SEND_INTERVAL = 2; //短信发送间隔时间

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sms}}';
    }

    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
            \bricksasp\common\OwnerIdBehavior::className()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mobile'], 'required'],
            [['mobile'], 'validMobile'],
            [['owner_id', 'mobile', 'status', 'type', 'created_at', 'updated_at'], 'integer'],
            [['message'], 'string', 'max' => 255],
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
            'mobile' => '手机号',
            'status' => 'Status',
            'message' => 'Message',
            'type' => 'Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function validMobile()
    {
        if (!Tools::is_mobile($this->mobile)) {
            $this->addError('mobile','请输入正确的手机号');
        }
    }

    /**
     * 发送短信
     * @param  int| array $mobile 
     * @param  string $message 消息内容
     * @param  int $msgtype 消息类型
     * @return array
     */
    public function sendsms($mobile, $message, $msgtype=self::TYPE_VCODE, $owner_id=null)
    {
        $dur = self::SMS_DURATION;
        $t = time();
        $sitm = $t - self::SMS_SEND_INTERVAL;
        $tm = time() - $dur;
        $ctm = $t;
        $key = "sms:{$mobile}";
        $v = ['dur'=>$dur, 'ctm'=>$ctm, 'rectm'=>$ctm];
        $str = json_encode($v);
        $script = <<<eof
        local tm = {$tm}
        local sitm = {$sitm}
local res=redis.call('GET','{$key}')
if res == false then
    redis.call('SET','{$key}','{$str}','EX', {$dur})
    return 1
end
local dt = cjson.decode(res)
if sitm > dt.rectm and tm <= dt.ctm   then
    dt.rectm = {$ctm}
    redis.call('SET', '{$key}', cjson.encode(dt))
    return 2
end
if tm <= dt.ctm then
    return 3
else
    redis.call('DEL','{$key}')
    redis.call('SET','{$key}','{$str}','EX', {$dur})
    return 1
end

eof;
        // Yii::$app->redis->executeCommand('DEL', [$key,0]);
        $status = Yii::$app->redis->executeCommand('EVAL', [$script,0]);
        if ($status == 3) {
            Tools::breakOff(920004);
        }
        $set = SmsSetting::find()->where(['owner_id'=>$owner_id, 'status'=>1])->one();
        if (!$set) {
            Tools::breakOff('未开启短信');
        }
        if (!$set->secret_id || !$set->secret_key) {
            Tools::breakOff('未设置短信密钥对');
        }

        $map['platform'] = $set->platform;
        if ($msgtype == self::TYPE_VCODE) {
            $map['code'] = array_column(SmsTpl::$defaultCode, 'code');
        }

        $tpl = SmsTpl::find()->where($map)->one();
        print_r(11/*$set->toArray()*/);exit();

        $model = Yii::createObject([
            'class' => 'bricksasp\\models\\sms\\' . $set->platform == 1 ? 'Tencent':'Ali',
            'sid' => $set->secret_id,
            'skey' => $set->secret_key,
            'tpl_id' => $tpl->tpl_id,
            'appid' => $tpl->appid,
            'sign' => $tpl->sign,
        ]);
        if ($status == 2) {
            $mes = self::find()->where(['and',
                ['mobile' => $mobile, 'status' => self::STATUS_SEND,'type'=>self::TYPE_VCODE],
                '`created_at`>=' .(time()-self::SMS_DURATION)
            ])->orderBy('created_at desc')->one();
        }

        if (empty($mes)) {
            $this->load([
                'message'    => $message, 
                'mobile'     =>$mobile, 
                'status'     =>self::STATUS_SEND, 
                'type'       =>$msgtype, 
                'created_at' =>$t, 
                'updated_at' =>$t
            ]);
            if (!$this->validate()) {
                return false;
            }
        }else{
            $mes->updated_at = $t;
            $mes->message = $message;
            if ($mes->save()) {
                //重发短信
                $res = $model->send(is_array($mobile) ? $mobile : [$mobile.''],[$mes->message, '' . ($dur/60)]);
                return $mes;
            }else{
                $this->setErrors($mes->errors);
                return false;
            }
        }

        // 发短信
        $model->send(is_array($mobile) ? $mobile : [$mobile],[$message, "" . ($dur/60)]);

        return $this->save();
    }

    /**
     * 验证码验证
     * @param  int $mobile 
     * @param  int $code   
     * @param  bool $writeOff   是否核销
     * @return array         
     */
    public function verificationCode($mobile, $code,$writeOff=false)
    {
        $uid = Yii::$app->user->identity->id ?? null;
        if ($uid) {
            $user = User::find()->select(['id'])->where(['phone' => $mobile])->one();
            if ($user->phone != $mobile && !empty($this->phone)) {
                Tools::breakOff(920003);
            }
        }
        $key = "sms:{$mobile}";
        $model = $this::find()->where(['and', ['mobile' => $mobile, 'message' => $code, 'status' => self::STATUS_SEND], '`created_at`>=' .(time()-self::SMS_DURATION)])->one();
        if ($model) {
            $model->status = self::STATUS_USED;
            if ($writeOff) {
                Yii::$app->redis->executeCommand('DEL', [$key,0]);
                return $model->save();
            }
            return true;
        }
        Tools::breakOff(980009);
    }
}
