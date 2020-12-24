<?php
namespace bricksasp\common;

use Yii;

/**
 * 服务开启检查
 */
class ServiceEvent extends \yii\base\BaseObject
{
	public static function checkRedis($event){
        try{
            Yii::$app->cache->exists('test');
        }catch(\Exception $e){
            $cache = [
                'class' => 'yii\caching\FileCache',
                'keyPrefix' => 'bricksasp_',
            ];
            Yii::$app->set('cache', $cache);
        }
    }
}