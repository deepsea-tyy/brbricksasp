<?php
namespace bricksasp\rbac\controllers;

use Yii;
use bricksasp\rbac\models\Route;
use yii\filters\VerbFilter;
use bricksasp\base\BackendController;
use bricksasp\base\Tools;

/**
 * Description of RuleController
 *
 * @author 649909457@qq.com
 * @since 1.0
 */
class RouteController extends BackendController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['post'],
                    'assign' => ['post'],
                    'remove' => ['post'],
                    'refresh' => ['post'],
                ],
            ]
        ]);
    }
    /**
     * Lists all Route models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Route();

        return $this->success($model->getRoutes());
    }

    /**
     * Creates a new AuthItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        Yii::$app->getResponse()->format = 'json';
        $routes = Yii::$app->getRequest()->post('route', '');
        $routes = preg_split('/\s*,\s*/', trim($routes), -1, PREG_SPLIT_NO_EMPTY);
        $model = new Route();
        $model->addNew($routes);
        return $this->success($model->getRoutes());
    }

    /**
     * Assign routes
     * @return array
     */
    public function actionAssign()
    {
        $routes = Yii::$app->getRequest()->post('routes', []);
        $model = new Route();
        $model->addNew($routes);
        Yii::$app->getResponse()->format = 'json';
        return $this->success($model->getRoutes());
    }

    /**
     * Remove routes
     * @return array
     */
    public function actionRemove()
    {
        $routes = Yii::$app->getRequest()->post('routes', []);
        $model = new Route();
        $model->remove($routes);
        Yii::$app->getResponse()->format = 'json';
        return $this->success($model->getRoutes());
    }

    /**
     * Refresh cache
     * @return type
     * 
     * @OA\Post(path="/rbac/route/refresh",
     *   summary="刷新路由缓存",
     *   tags={"管理后台权限接口"},
     *   
     *   @OA\Parameter(required=true,description="用户请求token",name="auth-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *         ref="#/components/schemas/response"
     *       )
     *     ),
     *   ),
     * )
     */
    public function actionRefresh()
    {
        $model = new Route();
        $model->invalidate();
        Yii::$app->getResponse()->format = 'json';
        return $this->success($model->getRoutes());
    }
}
