<?php

namespace bricksasp\runerrands\controllers;

use Yii;
use ciniran\excel\ReadExcel;
use bricksasp\models\School;

class UserController extends \bricksasp\base\BaseController
{

	public function noLoginAction()
	{
		return [
			'index',
		];
	}

    public function actionIndex()
    {
    	// $path = '/Users/tyy/Downloads/school.xls';
     //    $excel = new ReadExcel([
     //        'path' => $path,
     //        'head' => true,
     //    ]);
     //    $data = $excel->getArray();
     //    $emp = [];
    	// $t = time();
     //    foreach ($data as $k => $v) {
     //    	if (empty($v['code'])) {
     //    		$emp[] = $v;
     //    		unset($data[$k]);
     //    	}else{
     //    		$data[$k]['created_at'] = $t;
     //    		$data[$k]['updated_at'] = $t;
     //    	}
     //    }
     //    Yii::$app->db->createCommand()->batchInsert(School::tableName(), array_keys(end($data)), $data)->execute();
        return $this->success();
    }

    
}
