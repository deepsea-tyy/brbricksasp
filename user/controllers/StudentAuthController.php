<?php

namespace bricksasp\user\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\redis\Token;
use bricksasp\models\StudentAuth;
use bricksasp\models\RunerrandsRider;
use bricksasp\models\Store;
use yii\data\ActiveDataProvider;
use bricksasp\base\BackendController;

class StudentAuthController extends BackendController
{
    public function loginAction()
    {
        return [
            'view',
            'create',
            'update',
        ];
    }

    /**
     * @OA\Get(path="/user/student-auth/index",
     *   summary="学生认证列表",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
     *   @OA\Parameter(name="is_rider",in="query",@OA\Schema(type="integer"),description="1骑手"),
     *   @OA\Parameter(name="is_incharge",in="query",@OA\Schema(type="integer"),description="1站长"),
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
        $with = ['schoolArea','school','realName','uinfo','user','orders'];
        $query =  StudentAuth::find();
        $query->andFilterWhere([
            'status' => $params['status']??null,
        ]);
        $query->andFilterWhere($this->ownerCondition());
        $query->orderBy('status asc');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        if (isset($params['is_rider'])) {
            $ids = RunerrandsRider::find()->select(['user_id'])->asArray()->all();
            $query->andFilterWhere([
                'user_id' => array_column($ids,'user_id'),
            ]);
        }
        if (isset($params['is_incharge'])) {
            $ids = Store::find()->select(['user_id'])->where(['type'=>3])->asArray()->all();
            $query->andFilterWhere([
                'user_id' => array_column($ids,'user_id')?array_column($ids,'user_id'):[0],
            ]);
        }

        $list = [];
        foreach ($dataProvider->models as $item) {
            $row = $item->toArray();
            foreach ($with as $field) {
                if ($field == 'orders') {
                    $row['orderCount'] = $item->$field ? number_format(array_sum(array_column($item->$field, 'pay_price')), 2, '.', ''):0;
                    $row['orderNum'] = $item->$field ? count($item->$field):0;
                    continue;
                }
                $row[$field] = $item->$field;
            }
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
     * @OA\Get(path="/user/student-auth/view",
     *   summary="学生认证详情",
     *   tags={"user模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token",),
     *   @OA\Parameter(name="user_id",in="query",@OA\Schema(type="integer"),description="user_id"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       
     *       @OA\Schema(ref="#/components/schemas/StudentAuthUpdate"),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = StudentAuth::findOne($this->updateCondition(['user_id'=>$params['user_id']??$this->current_user_id]));
        if (!$model) {
            Tools::breakOff(empty($params['auth']) ? 400001 : 40001);
        }

        $data = $model->toArray();
        $data['school'] = $model->school;
        $data['schoolArea'] = $model->schoolArea;
        if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND) {
            $data['frontalPhoto'] = $model->studentIdCardFrontalPhoto;
            $data['reversePhoto'] = $model->studentIdCardReversePhoto;
        }else{
            $data['realName'] = $model->realName;
            $data['realName'] = $model->realName;
            $data['uinfo'] = $model->uinfo;
            $data['user'] = $model->user;
            $data['fund'] = $model->fund;
            if ($model->rider) {
                $rider = $model->rider->toArray();
                $rider['school'] = $model->rider->school;
                $rider['schoolArea'] = $model->rider->schoolArea;
                $data['rider'] = $rider;
            }
            if ($model->store) {
                $store = $model->store->toArray();
                if ($model->store->schoolRelation->type == 2) {
                    $store['schoolArea'] = $model->store->school;
                    $store['school'] = $model->store->school->school;
                }else{
                    $store['school'] = $model->store->school;
                }
                $data['store'] = $store;
            }

            $orders = $model->orders;

            $data['orderCount'] = $orders ? number_format(array_sum(array_column($orders, 'pay_price')), 2, '.', ''):'0.00';
            $data['orderNum'] = $orders ? count($orders):0;
            $last = end($orders);
            $data['lastOrderTime'] = $last ? $last['created_at']:0;
        }
        
        return $this->success($data);
    }

    /**
     * @OA\Post(path="/user/student-auth/create",
     *   summary="创建学生认证",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token",),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/StudentAuthCreate"
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
     * @OA\Schema(
     *   schema="StudentAuthCreate",
     *   description="学生认证",
     *   @OA\Property(property="school_id", type="integer", description="主校id"),
     *   @OA\Property(property="school_area_id", type="integer", description="校区id"),
     *   @OA\Property(property="faculty", type="string", description="院系"),
     *   @OA\Property(property="subject", type="string", description="专业",),
     *   @OA\Property(property="enrollment_at", type="string", description="入学时间"),
     *   @OA\Property(property="student_id", type="integer", description="学号"),
     *   @OA\Property(property="student_id_card_frontal_photo", type="string", description="学生证正面照"),
     *   @OA\Property(property="student_id_card_reverse_photo", type="string", description="学生证反面照"),
     *   @OA\Property(property="status", type="integer", description="0未审核1通过2拒绝"),
     *   @OA\Property(property="refuse_reasons", type="string", description="拒绝原因"),
     *   required={"name"}
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new StudentAuth();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/user/student-auth/update",
     *   summary="修改学生认证",
     *   tags={"user模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token",),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/StudentAuthUpdate"
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
     *   schema="StudentAuthUpdate",
     *   description="学生认证数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="user_id", type="integer", description="user_id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/StudentAuthCreate"),
     *   }
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel($this->updateCondition(empty($params['user_id']) ? [] : ['user_id'=>$params['user_id']]));

        if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND) {
            $params['status'] = 0;
        }
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/user/student-auth/delete",
     *   summary="删除学生认证",
     *   tags={"user模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token",),
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
     *
     * 
     */
    public function actionDelete()
    {
        $params = $this->queryMapPost();

        $transaction = StudentAuth::getDb()->beginTransaction();
        try {
            StudentAuth::deleteAll($this->updateCondition(['user_id'=>$params['ids']??0]));
            $transaction->commit();
            return $this->success();
        } catch(\Throwable $e) {
            $transaction->rollBack();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Finds the StudentAuth model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return StudentAuth the loaded model
     */
    protected function findModel($id)
    {
        if (($model = StudentAuth::findOne($id)) !== null) {
            return $model;
        }

        Tools::breakOff(40001);
    }
}
