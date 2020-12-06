<?php
namespace bricksasp\base;

use Yii;

trait BaseTrait {
    
    public function success($data=[], $msg='SUCCESS')
    {
        return [
            'code' => 200,
            'message' => $msg,
            'data' => $data
        ];
    }

    public function fail($msg='FAIL', $ecode=50000)
    {
        if (is_array($msg)) {
            $ecode = 0;
        }
        return [
            'code' => 400,
            'errorCode' => $ecode,
            'message' => $msg
        ];
    }

    public function tsuccess($data=[], $c='default' , $a='default' , $msg='ok')
    {
        return json_encode(['controller' => $c, 'action' => $a, 'code'=>200, 'message'=>$msg, 'data'=>$data],JSON_UNESCAPED_UNICODE);
    }

    public function tfail($c='default' , $a='default' , $msg='FAIL', $ecode=50000)
    {
        if (is_array($msg)) {
            $ecode = 0;
        }
        return json_encode(['controller' => $c, 'action' => $a, 'code'=>400, 'message'=>$msg, 'errorCode' => $ecode],JSON_UNESCAPED_UNICODE);
    }
}