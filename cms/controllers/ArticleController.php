<?php
namespace bricksasp\cms\controllers;

use Yii;
use bricksasp\base\Tools;
use bricksasp\models\redis\Token;
use bricksasp\cms\models\Article;
use bricksasp\base\BackendController;
use bricksasp\cms\models\ArticleLabel;
use bricksasp\cms\models\ArticleSearch;
use bricksasp\cms\models\ArticleUserLog;
use bricksasp\cms\models\ArticleCategory;

/**
 * ArticleController implements the CRUD actions for Article model.
 */
class ArticleController extends BackendController
{
    public function checkLoginAction()
    {
        return [
            'index',
            'view'
        ];
    }

    public function noLoginAction()
    {
        return [
            'index',
            'view',
        ];
    }

    /**
     * @OA\Get(path="/cms/article/index",
     *   summary="文章列表",
     *   tags={"cms模块"},
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
        $params = array_merge($params,$this->ownerCondition());
        if ($this->current_login_type == Token::TOKEN_TYPE_FRONTEND) {
            $fields = ['id', 'title', 'subtitle', 'image', 'brief', 'content', 'release_at'];
        }else{
            $fields = [];
        }

        $params['with']= ['labels','file'];

        $searchModel = new ArticleSearch();
        $dataProvider = $searchModel->search($params,$fields);

        $list = [];
        foreach ($dataProvider->models as $item) {
            $row = $item->toArray();
            $row['labels'] = $item->labels;
            $row['file'] = $item->file;
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
     * @OA\Get(path="/cms/article/view",
     *   summary="文章详情",
     *   tags={"cms模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   @OA\Parameter(name="id",in="query",@OA\Schema(type="integer"),description="id"),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       
     *       @OA\Schema(ref="#/components/schemas/articleUpdate"),
     *     ),
     *   ),
     * )
     * 
     * @OA\Schema(
     *   schema="articleView",
     *   description="文章数据详情",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="labels", type="array", description="封面", @OA\Items(ref="#/components/schemas/label")),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/articleUpdate"),
     *   }
     * )
     */
    public function actionView()
    {
        $params = $this->queryMapGet();
        $model = $this->findModel($params['id'] ?? 0);
        $data = $model->toArray();
        Article::updateAllCounters(['view_num'=>1],['id'=>$params['id']]);
        if ($this->current_user_id && $this->current_login_type == Token::TOKEN_TYPE_FRONTEND) {
            $m = new ArticleUserLog();
            $m->load(['user_id'=>$this->current_user_id,'article_id'=>$params['id'],'created_at'=>time()]);
            $m->save();
        }
        $amodel = new ArticleCategory();
        $cascader = $amodel->cascader($model->cat_id);
        $data['cat_id'] = array_column($cascader, 'id');
        $data['file'] = $model->file??[];
        $data['labels'] = $model->labels;
        return $this->success($data);
    }

    /**
     * @OA\Post(path="/cms/article/create",
     *   summary="创建文章",
     *   tags={"cms模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/articleCreate"
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
     *   schema="articleCreate",
     *   description="文章",
     *   @OA\Property(property="cat_id", type="array", description="分类id",items={}),
     *   @OA\Property(property="title", type="string", description="标题"),
     *   @OA\Property(property="subtitle", type="string", description="标题"),
     *   @OA\Property(property="author", type="string", description="作者"),
     *   @OA\Property(property="reprint_info", type="string", description="转载说明"),
     *   @OA\Property(property="keywords", type="string", description="关键字"),
     *   @OA\Property(property="brief", type="string", description="文章摘要"),
     *   @OA\Property(property="content", type="string", description="文章内容"),
     *   @OA\Property(property="comments_num", type="integer", description="评论数"),
     *   @OA\Property(property="view_num", type="integer", description="浏览数"),
     *   @OA\Property(property="like_num", type="integer", description="点赞数"),
     *   @OA\Property(property="is_comment", type="integer", description="评论 1允许 2不允许"),
     *   @OA\Property(property="is_recommend", type="integer", description="推荐 1是 2否"),
     *   @OA\Property(property="status", type="integer", description="1正常 2未通过审核"),
     *   @OA\Property(property="labels", type="array", description="标签",items={}),
     *   required={"title", "content"}
     * )
     */
    public function actionCreate()
    {
        $params = $this->queryMapPost();
        $model = new Article();

        if ($model->saveData($params)) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * @OA\Post(path="/cms/article/update",
     *   summary="修改文章",
     *   tags={"cms模块"},
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         ref="#/components/schemas/articleUpdate"
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
     *   schema="articleUpdate",
     *   description="文章数据",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="id"),
     *     ),
     *     @OA\Schema(ref="#/components/schemas/articleCreate"),
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
     * @OA\Post(path="/cms/article/delete",
     *   summary="删除文章",
     *   tags={"cms模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
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
        if (Article::updateAll(['is_delete'=>1, 'updated_at'=>time()],$this->updateCondition(['id'=>$params['ids']??0, 'is_delete'=>0]))) {
            return $this->success();
        }

        $transaction = Article::getDb()->beginTransaction();
        $id = $params['id'] ?? 0;
        try {
            Article::deleteAll($this->updateCondition(['id'=>$params['ids']??0]));
            ArticleLabel::deleteAll(['article_id' => $params['ids']??0]);
            $transaction->commit();
            return $this->success();
        } catch(\Throwable $e) {
            $transaction->rollBack();
        }

        return $this->fail();
    }

    /**
     * @OA\Post(path="/cms/article/setlabel",
     *   summary="设置文章标签",
     *   tags={"cms模块"},
     *   
     *   @OA\Parameter(name="access-token",in="header",@OA\Schema(type="string"),description="用户请求token"),
     *   
     *   @OA\RequestBody(
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="article_id", type="integer", description="文章id"),
     *         @OA\Property(property="label_ids", type="array", description="标签id ", items={}),
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
    public function actionSetlabel()
    {
        extract(Yii::$app->request->post());
        $labels = [];
        foreach ($label_ids as $k => $item) {
            $label['article_id'] = $article_id;
            $label['label_id'] = $item;
            $label['sort'] = $k + 1;
            $labels[] = $label;
        }

        ArticleLabel::deleteAll(['article_id' => $article_id]);
        ArticleLabel::getDb()->createCommand()
                ->batchInsert(ArticleLabel::tableName(),array_keys(end($labels)),$labels)
                ->execute();
        return $this->success();
    }

    /**
     * Finds the Article model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Article the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Article::findOne($id)) !== null) {
            return $model;
        }
        Tools::breakOff(40001);
    }
}
