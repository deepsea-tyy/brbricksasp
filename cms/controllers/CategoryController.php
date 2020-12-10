<?php
namespace bricksasp\cms\controllers;

use Yii;
use bricksasp\base\Tools;
use yii\data\ActiveDataProvider;
use bricksasp\models\redis\Token;
use bricksasp\base\BackendController;
use bricksasp\cms\models\ArticleCategory;

/**
 * CategoryController implements the CRUD actions for ArticleCategory model.
 */
class CategoryController extends BackendController
{

    /**
     * @OA\Get(path="/cms/category/index",
     *   summary="文章分类列表",
     *   tags={"cms模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\Parameter(name="page",in="query",@OA\Schema(type="integer"),description="当前叶数"),
     *   @OA\Parameter(name="pageSize",in="query",@OA\Schema(type="integer"),description="每页行数"),
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
     * 
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->get();
        $query = ArticleCategory::find();

        $query->andFilterWhere($this->ownerCondition());
        if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND && empty($params['user_data'])) {
            $query->andFilterWhere(['status' => 1]);
        }
        $query->andFilterWhere(['is_delete'=> empty($params['is_delete']) ? 0 : 1]);
        $query->orderBy(['sort' => SORT_ASC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query->with(['file']),
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
     * @OA\Get(path="/cms/category/view",
     *   summary="文章分类详情",
     *   tags={"cms模块"},
     *   
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="id"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/cmsCatUpdate"
     *       ),
     *     ),
     *   ),
     * )
     */
    public function actionView()
    {
        $params = Yii::$app->request->get();
        $model = $this->findModel($params['id'] ?? 0);
        return $this->success($model);
    }

    /**
     * @OA\Get(path="/cms/category/tree",
     *   summary="文章分类树",
     *   tags={"cms模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="分类id,返回对应id子树"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/cmsCategoryTree"),
     *     ),
     *   ),
     * )
     *
     * @OA\Schema(
     *   schema="cmsCategoryTree",
     *   description="文章分类",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="分类id"),
     *       @OA\Property(property="name", type="string", description="分类名称"),
     *       @OA\Property(property="parent_id", type="integer", description="父id"),
     *       @OA\Property( property="children", type="array", description="子集", @OA\Items(
     *            @OA\Property(property="id", type="integer", description="分类id"),
     *            @OA\Property( property="name", type="string", description="名称"),
     *            @OA\Property(property="parent_id", type="integer", description="父id"),
     *         ),
     *       ),
     *     )
     *   }
     * )
     * 
     */
    public function actionTree()
    {
        $params = Yii::$app->request->get();
        $map = [];
        if (!empty($params['id'])) {
            $map['parent_id'] =  (int)$params['id'];
        }

        $query = ArticleCategory::find();
        if ($this->current_login_type == Token::TOKEN_TYPE_BACKEND) {
            $data = ArticleCategory::find()->select(['id', 'parent_id', 'name'])
                ->andWhere($map)
                ->asArray()
                ->all();
            $data = array_map(function ($item)
            {
                return $item->toArray();
            }, $data);
        }else{
            $data = ArticleCategory::find()
                ->select(['id', 'parent_id', 'name','image'])
                ->andWhere($map)
                ->asArray()
                ->all();
        }
        if ($map) {
            return $this->success($data);
        }
        $tree = Tools::build_tree($data);
        return $this->success($tree);
    }

    /**
     * @OA\Post(path="/cms/category/create",
     *   summary="创建文章分类",
     *   tags={"cms模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/cmsCatCreate"
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
     *   schema="cmsCatCreate",
     *   description="文章分类",
     *   @OA\Property(property="name", type="string", description="分类名称"),
     *   @OA\Property(property="parent_id", type="integer", description="父id"),
     *   @OA\Property(property="sort", type="integer", description="排序"),
     *   @OA\Property(property="image", type="string", description="封面图"),
     *   @OA\Property(property="code", type="string", description="调用代码"),
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new ArticleCategory();
        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/cms/category/update",
     *   summary="修改文章分类",
     *   tags={"cms模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),required=true,description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/cmsCatUpdate"
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
     *   schema="cmsCatUpdate",
     *   description="文章分类",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/cmsCatCreate"),
     *   }
     * )
     */
    public function actionUpdate()
    {
        $params = $this->queryMapPost();
        $model = $this->findModel($this->updateCondition(['id'=>$params['id'] ?? 0]));

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/cms/category/delete",
     *   summary="删除文章分类",
     *   tags={"cms模块"},
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
     *
     * 
     */
    public function actionDelete()
    {
        $params = $this->queryMapPost();
        if (ArticleCategory::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }
        return ArticleCategory::deleteAll($this->updateCondition(['id'=>$params['ids']??0])) ? $this->success() : Tools::breakOff(40001);
    }

    protected function findModel($id)
    {
        if (($model = ArticleCategory::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
    }
}
