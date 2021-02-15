<?php

namespace bricksasp\runerrands\controllers;

use Yii;
use bricksasp\base\Tools;
use ciniran\excel\ReadExcel;
use bricksasp\models\Order;
use bricksasp\rbac\models\User;
use bricksasp\models\UserFundLog;
use bricksasp\models\RunerrandsRider;
use bricksasp\models\OrderRunerrandsCancel;

class UserController extends \bricksasp\base\BackendController
{

	public function loginAction()
	{
		return [
			'repassword',
            'statistics',
		];
	}

    /**
     * @OA\Get(path="/runerrands/user/statistics",
     *   summary="跑腿统计",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(name="type",in="query",@OA\Schema(type="integer"),description="0今天1昨天2本月3上月"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/pagination"),
     *     ),
     *   ),
     * )
     */
    public function actionStatistics()
    {
        $type = Yii::$app->request->get('type',0);
        if ($type == 0) {
            $start = strtotime(date('Y-m-d',time()));
            $end = strtotime(date('Y-m-d',time()) . ' +1 day');
        }
        if ($type == 1) {
            $start = strtotime(date('Y-m-d',time()) . ' -1 day');
            $end = strtotime(date('Y-m-d',time()));
        }
        if ($type == 2) {
            $start = strtotime(date('Y-m',time()));
            $end = strtotime(date('Y-m',time()) . ' +1 month');
        }
        if ($type == 3) {
            $start = strtotime(date('Y-m',time()) . ' -1 month');
            $end = strtotime(date('Y-m',time()));
        }
        $map = [
            'and',
            ['between', 'created_at', $start, $end],
            [
                'or',
                ['receiver'=>$this->current_user_id],
                ['transit'=>$this->current_user_id],
            ]
        ];
        $models = Order::find()->select(['receiver','transit'])->where($map)->all();
        $data = [
            'succ' => 0,
            'all' => 0,
            'start' => date('Y-m-d H:i',$start),
            'end' => date('Y-m-d H:i',$end),
        ];
        foreach ($models as $item) {
            if ($item->receiver == $this->current_user_id) {
                $data['succ'] += 1;
            }
            $data['all'] += 1;
        }
        $map = [
            'and',
            ['status'=>1, 'scene'=>1,'user_id'=>$this->current_user_id],
            ['between', 'created_at', $start, $end],
        ];
        $data['money'] = UserFundLog::find()->where($map)->sum('point')??0;

        $map = [
            'and',
            ['user_id'=>$this->current_user_id],
            ['between', 'created_at', $start, $end],
        ];
        $data['cancel'] = OrderRunerrandsCancel::find()->where($map)->count();
        return $this->success($data);
    }

    /**
     * @OA\Post(path="/runerrands/user/repassword",
     *   summary="修改密码",
     *   tags={"跑腿模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="password", type="integer", description="password"),
     *         @OA\Property(property="newpassword", type="integer", description="newpassword"),
     *         @OA\Property(property="repassword", type="integer", description="repassword"),
     *       )
     *     )
     *   ),
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
    public function actionRepassword()
    {
        $password = Yii::$app->request->post('password');
        $newpassword = Yii::$app->request->post('newpassword');
        $repassword = Yii::$app->request->post('repassword');
        if (!$newpassword || !$repassword || $newpassword!=$repassword) {
            return $this->fail('确认密码不正确或密码为空');
        }

        $model = RunerrandsRider::find()->where(['user_id'=>$this->current_user_id])->one();
        if ($password && ($model->password == md5($password))) {
            $model->password = $newpassword;
            if ($model->save()) {
                User::destroyApiToken(Yii::$app->request->getHeaders()->get('access-token'));
                Tools::breakOff(50001);
            }
            return $this->fail($model->errors);
        }
        return $this->fail('原密码不正确');
    }

}
