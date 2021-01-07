<?php

namespace bricksasp\user\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\Shortener;

class InviteController extends \bricksasp\base\BackendController
{
    /**
     * @OA\Get(path="/user/invite/share",
     *   summary="分享邀请码",
     *   tags={"user模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="小程序用户登录凭证"),
     *   @OA\Parameter(name="scene",in="query",@OA\Schema(type="integer"),description="应用场景1默认官方官网小程序 2小程序二 3小程序三 4其他",example=1),
     *   @OA\Parameter(name="type",in="query",@OA\Schema(type="integer"),description="1商品2文章3酒卡4个人",example=4),
     *   @OA\Parameter(name="object_id",in="query",@OA\Schema(type="integer"),description="分享内容id",example=""),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *           @OA\Property(property="sc",type="string",description="分享码"),
     *         ),
     *     ),
     *   ),
     * )
     */
    public function actionShare()
    {
    	$params = $this->queryMapGet();
    	$map['scene'] = $params['scene']??1;
    	$map['type'] = $params['type']??4;
    	if (!empty($params['object_id'])) {
    		$map['object_id'] = $params['object_id'];
    	}

    	$model = Shortener::find()->where($map)->one();
    	if ($model) {
    		return $this->success(['sharecode'=>$model->code]);
    	}
    	$model = new Shortener();
        return $model->saveData($params)? $this->success(['sharecode'=>$model->code]) : $this->fail($model->errors);
    }

}
