<?php

namespace bricksasp\user\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\redis\Token;
use bricksasp\models\StudentAuth;
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
        $query =  StudentAuth::find();
        $query->andFilterWhere([
            'status' => $params['status']??null,
        ]);
        $query->andFilterWhere($this->ownerCondition());

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $this->success([
          'list' => $dataProvider->models,
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
        $model = $this->findModel($this->updateCondition(['user_id'=>$params['user_id']??$this->current_user_id]));
        $data = $model->toArray();
        $data['frontalPhoto'] = $model->studentIdCardFrontalPhoto;
        $data['reversePhoto'] = $model->studentIdCardReversePhoto;
        $data['school'] = $model->school;
        $data['schoolArea'] = $model->schoolArea;
        
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
