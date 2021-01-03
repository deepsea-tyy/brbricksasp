<?php

namespace bricksasp\pbl\controllers;

use Yii;
use Endroid\QrCode\QrCode;

class QrController extends \bricksasp\base\FrontendController
{
    // use GatewayClient\Gateway;
    //         list($yii_config, $worker_config) = require_once __DIR__ . '/../worker/config.php';
    //         Gateway::$registerAddress = $worker_config['registerAddress'];

	public function loginAction() {
		return [
            'scan-work',
            'params',
		];
	}
    
    public function noLoginAction() {
        return [
            'img',
            'barcode',
            'scanjmp',
            'jmpwork',
        ];
    }
    
    /**
     * @OA\Get(path="/pbl/qr/params",
     *   summary="二维码加密参数",
     *   description="地址多个key-val： url?key=1&key2=2",
     *   tags={"pbl模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     */
    public function actionParams()
    {
        $params = Yii::$app->request->get();
        $params['user_id'] = $this->current_user_id;
        return $this->success(base64_encode(serialize($params)));
    }

    /**
     * @OA\Get(path="/pbl/qr/img",
     *   summary="生成二维码",
     *   tags={"pbl模块"},
     *   @OA\Parameter(name="content",in="query",required=true,@OA\Schema(type="string"),description="内容"),
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     */
    public function actionImg()
    {
        $qrCode = new QrCode(Yii::$app->request->get('content'));
        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
        exit();
    }

    /**
     * @OA\Get(path="/pbl/qr/barcode",
     *   summary="生成条形码",
     *   tags={"pbl模块"},
     *   @OA\Parameter(name="barcode",in="query",required=true,@OA\Schema(type="integer"),description="条码号"),
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     */
    public function actionBarcode()
    {
        $redColor = [0, 0, 0];
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        header('Content-Type: image/png');
        echo $generator->getBarcode(Yii::$app->request->get('barcode'), $generator::TYPE_CODABAR, 3, 50, $redColor);
        exit();
    }

    /**
     *
     * @OA\Get(path="/pbl/qr/scanjmp",
     *   summary="微信扫码统一跳转地址",
     *   tags={"pbl模块"},
     *   @OA\Response(
     *     response=200,
     *     description="响应",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(ref="#/components/schemas/response")
     *     ),
     *   ),
     * )
     */
    public function actionScanjmp()
    {
        $params = Yii::$app->request->get();
        // var_dump($params);exit;
        $params['to'] = '/pbl/qr/jmpwork';
        return $this->redirect(array_merge(['/wxweb/login'],$params));
    }

    /**
     * 扫码后回调处理
     */
    public function actionJmpwork()
    {

    }
}
