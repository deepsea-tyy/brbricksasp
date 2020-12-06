<?php
namespace bricksasp\models\redis;

use Yii;

/**
 * This is the model class for collection "Token".
 */
class Token extends \yii\redis\ActiveRecord
{
    const TOKEN_DURATION = 72000;    // 有效时间
    const TOKEN_TYPE_FRONTEND = 1;  // 前台用户
    const TOKEN_TYPE_BACKEND = 2;   // 后台用户
    const TOKEN_TYPE_ACCESS = 3;    // 访问对应用户数据标识


    const IDENTITY_AUTHORIZE_LOOK = 1;   // 查看数据授权
    const IDENTITY_AUTHORIZE_CURD = 2;   // 操作数据授权

    const TOKEN_ENTRANCE_PC = 1;    // pc端入口
    const TOKEN_ENTRANCE_PM = 2;    // 移动端入口

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return [
            'user_id',
            'owner_id',
            'token',
            'type',
            'duration',
            'entrance',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'owner_id', 'token', 'type', 'duration'], 'required'],
            ['type', 'in', 'range' => [self::TOKEN_TYPE_FRONTEND, self::TOKEN_TYPE_BACKEND, self::TOKEN_TYPE_ACCESS]],
            [['entrance'], 'default', 'value' => self::TOKEN_ENTRANCE_PM]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'user_id',
            'owner_id' => 'owner_id',
            'token' => 'token',
            'type' => 'type',
            'duration' => 'duration',
        ];
    }

    public static function find($token=null)
    {
        $model = new self();
        $res = $model::getDb()->executeCommand('GET', [$model->keyPrefix() . ':' . $token]);
        $model->load(json_decode($res, true),'');
        return $model;
    }

    /**
     * 单点登录
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = NULL)
    {
        $hkey = $this->keyPrefix() . ':' . $this->token;
        $values = $this->getDirtyAttributes($this->attributes());
        
        unset($values['token']);
        $valJson = json_encode($values);
        $duration = self::TOKEN_DURATION;
        $script = <<<eof
local allkeys=redis.call('SMEMBERS','token')
for k,v in ipairs(allkeys) do
    local res=redis.call('GET','{$this->keyPrefix()}' .. ':' .. v)
    if type(res) == 'string' then
        local td = cjson.decode(res)
        if (td.user_id=={$values['user_id']} and td.type == {$values['type']}) then
            redis.call('DEL','{$this->keyPrefix()}' .. ':' .. v)
        end
    else
        redis.call('SREM','token', v)
    end
end
redis.call('SET','{$hkey}', '{$valJson}', 'EX', {$duration})
return redis.call('SADD','token', '{$this->token}')
eof;
        return self::getDb()->executeCommand('EVAL', [$script,0]);
    }

    /**
     * 销毁token
     */
    public static function destroyToken($token)
    {
        return self::getDb()->executeCommand('DEL', [$model->keyPrefix() . ':' . $token]);
    }


    /**
     * 更新token
     * @param $params
     * @param $request
     * @return array|bool|null|string
     * @throws \yii\db\Exception
     */
    public static function updateToken($params,$request){
        $authHeader = $request->getHeaders()->get('access-token');
        $token = self::find($authHeader);
        $tokenArr = $token->toArray();
        $end = array_merge($tokenArr,$params);
        $duration = self::TOKEN_DURATION;
        $hkey = self::keyPrefix() . ':' . $authHeader;
        $valJson = json_encode($end);
        $script = "redis.call('SET','{$hkey}', '{$valJson}', 'EX', {$duration})";
        return self::getDb()->executeCommand('EVAL', [$script,0]);
    }

    /**
     * 清除token
     * @param  int $user_id 
     * @return int   1:成功
     */
    public function clear($user_id)
    {
        $hkey = $this->keyPrefix() . ':' . $this->token;
        $script = <<<eof
local allkeys=redis.call('SMEMBERS','token')
for k,v in ipairs(allkeys) do
    local res=redis.call('GET','{$this->keyPrefix()}' .. ':' .. v)
    if type(res) == 'string' then
        local td = cjson.decode(res)
        if (td.user_id=={$user_id}) then
            redis.call('DEL','{$this->keyPrefix()}' .. ':' .. v)
            return 1
        end
    end
end
eof;
        return self::getDb()->executeCommand('EVAL', [$script,0]);
    }
}