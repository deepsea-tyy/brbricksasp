<?php

namespace bricksasp\models;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\User;

/**
 * This is the model class for table "{{%sms}}".
 *
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $mobile
 * @property int|null $status 使用状态0未发送1已发送2已使用
 * @property string|null $content
 * @property int|null $type 1验证码2其他信息
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Sms extends \bricksasp\base\BaseActiveRecord
{
    const TYPE_VCODE_PATTERN = 1;//通用验证码
    const TYPE_VCODE_LOGIN = 2;//登录验证码
    const TYPE_VCODE_REGISTER = 3;//注册验证码

    const TYPE_CONTENT = 2;//短信消息
    const STATUS_NO_SEND = 0; //未发送
    const STATUS_SEND = 1; //已发送
    const STATUS_USED = 2; //已使用
    const SMS_DURATION = 600; //短信过期时间
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
            [['content'], 'string', 'max' => 255],
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
            'content' => 'Content',
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
     * @param  string $content 消息内容
     * @param  int $type 消息类型
     * @return array
     */
    public function sendsms($mobile, $content, $type=self::TYPE_VCODE_PATTERN, $owner_id=null)
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

        $map['owner_id'] = $owner_id;
        $mbl = is_array($mobile) ? $mobile : [$mobile.''];//电话号码
        if (in_array($type, [self::TYPE_VCODE_PATTERN,self::TYPE_VCODE_LOGIN,self::TYPE_VCODE_REGISTER,])) {
            $msg = [$content, '' . ($dur/60)];
        }
        if ($type == self::TYPE_VCODE_PATTERN) {
            $map['code'] = 'TPL_VCODE_PATTERN';
        }
        if ($type == self::TYPE_VCODE_LOGIN) {
            $map['code'] = 'TPL_VCODE_LOGIN';
        }
        if ($type == self::TYPE_VCODE_REGISTER) {
            $map['code'] = 'TPL_VCODE_REGISTER';
        }
        $tpl = SmsTpl::find()->where($map)->one();

        if (!$tpl) {
            Tools::breakOff('未设置短信密模版');
        }

        $model = Yii::createObject([
            'class' => 'bricksasp\\models\\sms\\' . ($set->platform == 1 ? 'Tencent':'Ali'),
            'sid' => $set->secret_id,
            'skey' => $set->secret_key,
            'tpl_id' => $tpl->tpl_id,
            'appid' => $tpl->appid,
            'sign' => $tpl->sign,
        ]);

        $data = [
            'owner_id'=> $owner_id, 
            'mobile'  =>$mobile, 
            'content' => $content, 
            'status'  =>self::STATUS_SEND, 
            'type'    =>$type,
            'created_at' =>$t, 
            'updated_at' =>$t
        ];
        $this->load($data);
        if ($this->save()) {
            $res = $model->send($mbl,$msg);
            return true;
        }
        return self;
    }

    /**
     * 验证码验证
     * @param  int $mobile 
     * @param  int $code
     * @param  int $type
     * @param  int $user_id
     * @param  bool $writeOff   是否核销
     * @return array         
     */
    public function checkVcode($mobile, $code, $type, $user_id, $writeOff=false)
    {
        if ($user_id) {
            $user = User::find()->select(['id'])->where(['phone' => $mobile])->one();
            if ($user->phone != $mobile && !empty($this->phone)) {
                Tools::breakOff(920003);
            }
        }
        $key = "sms:{$mobile}";
        $model = $this::find()->where(['and', ['mobile' => $mobile, 'content' => $code, 'status' => self::STATUS_SEND], '`created_at`>=' .(time()-self::SMS_DURATION)])->one();
        if ($model) {
            if ($writeOff) {
                $model->status = self::STATUS_USED;
                Yii::$app->redis->executeCommand('DEL', [$key,0]);
                return $model->save();
            }
            return true;
        }
        Tools::breakOff(980009);
    }
}
