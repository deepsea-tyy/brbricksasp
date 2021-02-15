<?php
namespace bricksasp\rbac\controllers;

use bricksasp\rbac\components\ItemController;
use yii\rbac\Item;
use Yii;
use bricksasp\rbac\models\AuthItem;
use bricksasp\rbac\models\searchs\AuthItem as AuthItemSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use bricksasp\rbac\components\Configs;
use bricksasp\rbac\components\MenuHelper;

/**
 * RoleController implements the CRUD actions for AuthItem model.
 *
 * @author 649909457@qq.com
 * @since 1.0
 */
class RoleController extends ItemController
{
    /**
     * @inheritdoc
     */
    public function labels()
    {
        return[
            'Item' => 'Role',
            'Items' => 'Roles',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Item::TYPE_ROLE;
    }

    /**
     * 
     * @OA\Get(path="/rbac/role/menu",
     *   summary="角色对应菜单",
     *   tags={"管理后台权限接口"},
     *   
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *           @OA\Property(description="菜单名",property="label",type="string"),
     *           @OA\Property(description="路由",property="route",type="string"),
     *           @OA\Property(description="子菜单",property="items",type="array",@OA\Items()),
     *         ),
     *       )
     *     ),
     *   ),
     * )
     */
    public function actionMenu()
    {
          $menus = MenuHelper::getAssignedMenu($this->user_id, $root = null, function ($menu)
          {
               $data = json_decode($menu['data'],true);
               return [
                   'label' => $data['description'],
                   'route' => [$menu['route']],
                   'items' => $menu['children']
               ];
          }, true);
          return $this->success($menus);
    }

    /**
     * 
     * @OA\Get(path="/rbac/role/index",
     *   summary="角色列表",
     *   tags={"管理后台权限接口"},
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *         @OA\Property(description="描述",property="description",type="string"),
     *         @OA\Property(description="角色名称",property="name",type="string"),
     *         @OA\Property(description="规则名称",property="rule_name",type="string"),
     *         @OA\Property(description="拓展数据",property="data",type="string")
     *       )
     *     ),
     *   ),
     * )
     
    
    /**
     * 
     * @OA\Post(path="/rbac/role/create",
     *   summary="创建角色",
     *   tags={"管理后台权限接口"},
     *   
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(description="描述",property="description",type="string"),
     *         @OA\Property(description="角色名称",property="name",type="string"),
     *         @OA\Property(description="规则名称",property="rule_name",type="string"),
     *         @OA\Property(description="拓展数据",property="data",type="string")
     *       )
     *     )
     *   ),
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
    
    /**
     * 
     * @OA\Post(path="/rbac/role/delete",
     *   summary="删除角色",
     *   tags={"管理后台权限接口"},
     *   
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *
     *   @OA\Parameter(required=true,description="角色名称",name="id",in="query",@OA\Schema(type="string")),
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
    
    /**
     * 
     * @OA\Get(path="/rbac/role/view",
     *   summary="角色权限详情",
     *   tags={"管理后台权限接口"},
     *
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *
     *   @OA\Parameter(required=true,description="角色名称",name="id",in="query",@OA\Schema(type="string")),
     *   
     * 
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/userRole"
     *       )
     *     ),
     *   ),
     * )
     */
    
    /**
     * 
     * @OA\Post(path="/rbac/role/assign",
     *   summary="给角色授权",
     *   tags={"管理后台权限接口"},
     *
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\Parameter(required=true,description="角色名称",name="id",in="query",@OA\Schema(type="string")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(description="角色名称 数组结构",property="items",type="array",@OA\Items(example="/route/route",))
     *       )
     *     )
     *   ),
     *   
     * 
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *         ref="#/components/schemas/userRole"
     *         ),
     *       )
     *     ),
     *   ),
     * )
     */
    
    /**
     * 
     * @OA\Post(path="/rbac/role/remove",
     *   summary="移除角色权限",
     *   tags={"管理后台权限接口"},
     *
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\Parameter(required=true,description="id",name="id",in="query",@OA\Schema(type="string")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(description="角色名称 数组结构",property="items",type="array",@OA\Items(example="/route/route",))
     *       )
     *     )
     *   ),
     *   
     * 
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *         ref="#/components/schemas/userRole"
     *         ),
     *       )
     *     ),
     *   ),
     * )
     */
    


    /**
     * 
     * @OA\Schema(
     *  schema="userRole",
     *  description="授权操作数据结构",
     *  @OA\Property(description="可用权限",property="available",type="object",
     *  @OA\Property(description="可用角色",property="role",type="array", @OA\Items(example="role",)),
     *  @OA\Property(description="可用路由",property="route",type="array", @OA\Items(example="/module/controller/action",)),),
     *  @OA\Property(
     *    description="已有权限",
     *    property="assigned",
     *    type="object",
     *    @OA\Property(description="已有角色",property="role",type="array", @OA\Items(example="role",)),
     *    @OA\Property(description="已有路由",property="route",type="array", @OA\Items(example="/module/controller/action",)),
     *  )
     * )
     * 
     * @OA\Schema(
     *  schema="routeList",
     *  description="路由列表",
     *  @OA\Property(description="web全部路由",property="available",type="array", @OA\Items(example="/module/controller/action",)),
     *  @OA\Property(description="可用路由",property="assigned",type="array", @OA\Items(example="/module/controller/action",)),),
     * )
     *
     * 
     */
    


    /**
     * 
     * @OA\Get(path="/rbac/route/index",
     *   summary="路由列表",
     *   tags={"管理后台权限接口"},
     *   
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *           ref="#/components/schemas/routeList"
     *         ),
     *       )
     *     ),
     *   ),
     * )
     */
    
    /**
     * 
     * @OA\Post(path="/rbac/route/assign",
     *   summary="添加路由",
     *   tags={"管理后台权限接口"},
     *   
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(description="角色名称 数组结构",property="routes",type="array",@OA\Items(example="/module/controller/action",))
     *       )
     *     )
     *   ),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *           
     *           ref="#/components/schemas/routeList"
     *         ),
     *       )
     *     ),
     *   ),
     * )
     */

    /**
     * 
     * @OA\Post(path="/rbac/route/create",
     *   summary="新建路由",
     *   tags={"管理后台权限接口"},
     *   
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(description="角色名称 数组结构",property="route",type="string",example="/route")
     *       )
     *     )
     *   ),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *           ref="#/components/schemas/routeList"
     *         ),
     *       )
     *     ),
     *   ),
     * )
     */
    
    /**
     * 
     * @OA\Get(path="/rbac/rule/index",
     *   summary="规则列表",
     *   tags={"管理后台权限接口"},
     *   
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(description="角色名称 数组结构",property="route",type="string",example="/route")
     *       )
     *     )
     *   ),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *           ref="#/components/schemas/routeList"
     *         ),
     *       )
     *     ),
     *   ),
     * )
     */
    

    /**
     * 
     * @OA\Post(path="/rbac/rule/create",
     *   summary="新建规则",
     *   tags={"管理后台权限接口"},
     *   
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(description="规则名称",property="name",type="string",),
     *         @OA\Property(description="规则文件 例子命名空间文件名: bricksasp\rbac\rule\TestRule",property="className",type="string",)
     *       )
     *     )
     *   ),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *           @OA\Property(description="规则文件",property="className",type="array",@OA\Items(example="bricksasp\rbac\rule\TestRule",)),
     *           @OA\Property(description="规则名称",property="name",type="string",)
     *         ),
     *       )
     *     ),
     *   ),
     * )
     */


    /**
     * 
     * @OA\Post(path="/rbac/rule/delete",
     *   summary="删除规则",
     *   tags={"管理后台权限接口"},
     *   
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   @OA\Parameter(required=true,description="规则名称",name="id",in="query",@OA\Schema(type="string")),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *           ref="#/components/schemas/response"
     *         ),
     *       )
     *     ),
     *   ),
     * )
     */


    /**
     * 
     * @OA\Get(path="/rbac/menu/index",
     *   summary="菜单列表",
     *   tags={"管理后台权限接口"},
     *   
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *           ref="#/components/schemas/response"
     *         ),
     *       )
     *     ),
     *   ),
     * )
     */

    /**
     * 
     * @OA\Post(path="/rbac/menu/create",
     *   summary="创建菜单",
     *   tags={"管理后台权限接口"},
     *   
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(description="父级id",property="parent",type="integer",),
     *         @OA\Property(description="菜单英文名",property="name",type="string",),
     *         @OA\Property(description="父级英文名",property="parent_name",type="string",),
     *         @OA\Property(description="菜单对应路由",property="route",type="string",),
     *         @OA\Property(description="排序",property="order",type="integer",),
     *         @OA\Property(description="额外设置",property="data",type="object",
     *           @OA\Property(description="描述，菜单中文名",property="description",type="string",),
     *           @OA\Property(description="显示图标",property="icon",type="string",),
     *         )
     *       )
     *     )
     *   ),
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *           ref="#/components/schemas/response"
     *         ),
     *       )
     *     ),
     *   ),
     * )
     */

    /**
     * 
     * @OA\Post(path="/rbac/menu/delete",
     *   summary="删除菜单",
     *   tags={"管理后台权限接口"},
     *   
     *   @OA\Parameter(required=true,description="用户请求token",name="access-token",in="header",@OA\Schema(type="string")),
     *   
     *   @OA\Parameter(required=true,description="菜单id",name="id",in="query",@OA\Schema(type="integer")),
     *   
     *   
     *   @OA\Response(
     *     response=200,
     *     description="响应结构",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *           ref="#/components/schemas/response"
     *         ),
     *       )
     *     ),
     *   ),
     * )
     */

    
}
