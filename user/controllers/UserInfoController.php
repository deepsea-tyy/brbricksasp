<?php
namespace bricksasp\user\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\UserInfo;
use bricksasp\models\UserFund;
use yii\data\ActiveDataProvider;
use bricksasp\base\BackendController;

/**
 * UserInfoController implements the CRUD actions for UserInfo model.
 */
class UserInfoController extends BackendController
{
	public function loginAction()
	{
		return [
			'view',
            'update',
            'fund',
		];
	}

    /**
     * @OA\Get(path="/user/user-info/index",
     *   summary="用户信息列表",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
     *   @OA\Parameter(name="type",in="query",@OA\Schema(type="integer"),description="1文章2地址3商品4商铺"),
     *   @OA\Parameter(name="is_delete",in="query",@OA\Schema(type="integer"),description="1软删除"),
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
    public function actionIndex()
    {
        $params = Yii::$app->request->get();
        $query = UserInfo::find();
        $query->andFilterWhere([
            'type' => $params['type']??null,
        ]);
        
        $query->andFilterWhere($this->ownerCondition());
        $query->andFilterWhere(['like', 'name', $params['name']??null]);
        $query->andFilterWhere(['is_delete'=> empty($params['is_delete']) ? 0 : 1]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query->with(['file'])->joinWith([
			    'order' => function ($query) {
			        $query->onCondition(['order.status' => Order::STATUS_ACTIVE]);
			    },
			]),
        ]);
        
        $list = [];
        foreach ($dataProvider->models as $item) {
            $row = $item->toArray();
            $row['file'] = $item->file ?? [];
            $list[] = $row;
        }
        return $this->success([
          'list' => $list,
          'pageCount' => $dataProvider->pagination->pageCount,
          'totalCount' => $dataProvider->pagination->totalCount,
          'page' => $dataProvider->pagination->page + 1,
          'pageSize' => $dataProvider->pagination->limit,
        ]);
    }
    
    /**
     * @OA\Get(path="/user/user-info/view",
     *   summary="用户信息详情",
     *   tags={"user模块"},
     *   
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="id"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       
     *       @OA\Schema(ref="#/components/schemas/UserInfoUpdate"),
     *     ),
     *   ),
     * )
     * 
     * @OA\Schema(
     *   schema="UserInfoView",
     *   description="用户信息数据详情",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="labelItems", type="array", description="封面", @OA\Items(ref="#/components/schemas/label")),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/UserInfoUpdate"),
     *   }
     * )
     */
    public function actionView()
    {
        $model = $this->findModel(Yii::$app->request->get('id')??['user_id'=>$this->current_user_id]);
        $data = $model->toArray();
        $data['file'] = $model->file;
        return $this->success($data);
    }

    /**
     * @OA\Get(path="/user/user-info/fund",
     *   summary="用户资产详情",
     *   tags={"user模块"},
     *   
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="id"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       
     *       @OA\Schema(ref="#/components/schemas/response"),
     *     ),
     *   ),
     * )
     */
    public function actionFund()
    {
        $model = UserFund::findOne(Yii::$app->request->get('id')??['user_id'=>$this->current_user_id]);
        return $this->success($model);
    }

    /**
     * @OA\Post(path="/user/user-info/update",
     *   summary="修改用户信息",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/UserInfoUpdate"
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
     * 
     * 
     * @OA\Schema(
     *   schema="UserInfoUpdate",
     *   description="用户信息",
     *   @OA\Property(property="user_id", type="string", description=""),
     *   @OA\Property(property="avatar", type="string", description="头像"),
     *   @OA\Property(property="name", type="string", description="姓名"),
     *   @OA\Property(property="nickname", type="string", description="nickname"),
     *   @OA\Property(property="birthday", type="integer", description="出生日期"),
     *   @OA\Property(property="age", type="integer", description="年龄"),
     *   @OA\Property(property="gender", type="integer", description="性别"),
     *   @OA\Property(property="vip", type="integer", description="会员等级"),
     *   @OA\Property(property="vip_duration", type="integer", description="会员到期时间"),
     *   @OA\Property(property="platform", type="integer", description="1站内用户2微信用户3支付宝用户4抖音用户"),
     *   @OA\Property(property="openid", type="string", description="openid"),
     *   @OA\Property(property="country", type="string", description="country"),
     *   @OA\Property(property="province", type="string", description="province"),
     *   @OA\Property(property="city", type="string", description="city"),
     *   @OA\Property(property="unionid", type="string", description="unionid"),
     *   @OA\Property(property="level", type="string", description="账号级别"),
     *   @OA\Property(property="company_id", type="string", description="公司id"),
     *   @OA\Property(property="uuid", type="string", description="uuid"),
     *   @OA\Property(property="mark", type="string", description="备注"),
     *   @OA\Property(property="type", type="string", description="注册入口 1普通会员2商家"),
     *   @OA\Property(property="scene", type="string", description="应用场景"),
     *   @OA\Property(property="school_id", type="integer", description="学校id"),
     *   required={"name"}
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel($this->updateCondition(['user_id'=>$params['user_id'] ?? $this->current_user_id]));

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/user/user-info/delete",
     *   summary="删除用户信息",
     *   tags={"user模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="ids", type="array", description="ids", @OA\Items()),
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
    public function actionDelete()
    {
        $params = $this->queryMapPost();
        if (UserInfo::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }
        return UserInfo::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }

    /**
     * Finds the UserInfo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserInfo the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserInfo::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
    }
}
