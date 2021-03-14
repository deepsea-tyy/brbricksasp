<?php

namespace bricksasp\backend\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\Setting;
use bricksasp\base\BackendController;

/**
 * SettingController implements the CRUD actions for Setting model.
 */
class SettingController extends BackendController {

	/**
      * 
      * @OA\Get(path="/backend/setting/index",
      *   summary="系统设置",
      *   tags={"backend模块"},
      *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token",required=true),
      *   @OA\Parameter(name="keyPrefix",in="query",@OA\Schema(type="string"),description="key前缀 WX:微信设置 MALL_RATIO:商城比例设置 MALL_ORDER:商城订单设置 MALL_VIP:商城会员等级 MALL_SPECIAL:商城会员特殊记名 MALL_OTHER:商城其他设置 SMS:短信设置 WEB:基础设置 PROJECT_COPYRIGHT:版权设置", example="WX"),
      *   
      *   @OA\Response(
      *     response=200,
      *     description="响应结构",
      *     @OA\MediaType(
      *       mediaType="application/json",
      *       @OA\Schema(
      *        ref="#/components/schemas/setting",
      *       )
      *     ),
      *   ),
      * )
      */
	public function actionIndex() {
            $data = Setting::getSetting($this->current_owner_id, Yii::$app->request->get('keyPrefix','WX'));
		return $this->success($data);
	}

     /**
      * @OA\Post(
      *   path="/backend/setting/update",
      *   summary="更新设置",
      *   description="",
      *   tags={"backend模块"},
      *   operationId="",
      *   @OA\Parameter(description="登录凭证",name="access-token",in="header",@OA\Schema(type="string")),
      *   @OA\RequestBody(
      *     required=true,
      *     @OA\MediaType(
      *       mediaType="application/json",
      *       @OA\Schema(
      *         @OA\Property(property="keyPrefix",type="string",description="键前缀",example="WX"),
      *         @OA\Property(property="key1",type="object",description="键值对",
      *           @OA\Property(property="name",type="string",description="名称"),
      *           @OA\Property(property="val",type="string",description="内容"),
      *           @OA\Property(property="ext1",type="string",description="拓展值"),
      *           @OA\Property(property="ext2",type="string",description="拓展值"),
      *         ),
      *       )
      *     )
      *   ),
      *   @OA\Response(
      *     response=200,
      *     description="文件结构",
      *     @OA\MediaType(
      *       mediaType="application/json",
      *       @OA\Schema(
      *        ref="#/components/schemas/response",
      *       ),
      *     ),
      *   ),
      * )
      * 
      */

	public function actionUpdate() {
        if (Setting::saveSetting(Yii::$app->request->post(),$this->current_owner_id, Yii::$app->request->post('keyPrefix'))) {
            return $this->success();
        }
        return $this->fail();
	}
}
