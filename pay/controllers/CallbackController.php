<?php
namespace bricksasp\pay\controllers;

use Yii;
use WeChat\Pay;
use WeChat\Contracts\Tools;
use bricksasp\models\Order;
use bricksasp\models\OrderPay;
use bricksasp\models\pay\Wechat;
use bricksasp\base\BaseController;
use bricksasp\jobs\OrderDistribution;

class CallbackController extends BaseController
{
    /**
     * @OA\Post(path="/pay/callback/wxnotify",
     *   summary="微信回调地址",
     *   tags={"pay模块"},
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     */
	public function actionWxnotify() {
		$xml = file_get_contents('php://input');
    	$data = array_filter(Tools::xml2arr($xml));

    	if (empty($data)) {
			return $this->asXml(['return_code' => 'FAIL', 'return_msg' => '无效请求']);
    	}
    	$map = json_decode(base64_decode($data['attach']),true);

        $pay = Yii::createObject([
            'class' => Wechat::className(),
            'owner_id' => $map['owner_id'],
            'user_id' => $map['user_id'],
            'scene' => $map['scene'],
        ]);
		$config = $pay->config();
		$wechat = \WeChat\Pay::instance($config);

	    if (isset($data['sign']) && $wechat->getPaySign($data) === $data['sign'] && $data['return_code'] === 'SUCCESS' && $data['result_code'] === 'SUCCESS') {
	        $model = OrderPay::find()->where(['id' => $data['out_trade_no']])->one();
	        if (!$model || $model->status == OrderPay::STATUS_SUCCESS) {
				return $this->asXml(['return_code' => 'FAIL', 'return_msg' => $model ? '已处理' : '支付单号无效']);
	        }
    		$transaction = OrderPay::getDb()->beginTransaction();
			try {
		        if (Order::updateAll(['pay_status' => Order::PAY_ALL,'pay_at'=>time()], ['id'=>$model->order_id]) === false || OrderPay::updateAll(['status' => OrderPay::STATUS_SUCCESS,'third_id'=>$data['transaction_id']],['id'=>$model->id]) === false) {
		        	$transaction->rollBack();
					return $this->asXml(['return_code' => 'FAIL', 'return_msg' => '更新失败']);
		        }
		        
				file_put_contents(Yii::getAlias('@runtime') . '/logs/pay.log', $xml . PHP_EOL,FILE_APPEND);
	            $transaction->commit();
	            Yii::$app->queue->push(new OrderDistribution(['order_id' => $model->order_id]));
				return $this->asXml(['return_code' => 'SUCCESS', 'return_msg' => 'OK']);
			} catch (Exception $e) {
                $transaction->rollBack();
			}
	    }

		return $this->asXml(['return_code' => 'FAIL', 'return_msg' => '签名无效']);
	}

	/**
	 * 阿里支付回调
	 * @return mixed
	 */
	public function actionAlinotify() {
		try {

		} catch (Exception $e) {
			return $e->getMessage();
		}

		return $ret;
	}

	/**
	 * 招行支付回调
	 * @return mixed
	 */
	public function actionCmbnotify() {
		try {

		} catch (Exception $e) {
			return $e->getMessage();
		}

		return $ret;
	}

	/**
	 * 支付成功后同步跳转页面
	 */
	public function actionPayed() {
		return $this->render('view', [
		]);
	}
}
