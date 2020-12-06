<?php

/**
 * @OA\Info(
 *   version="1.0",
 *   title="比翼云 接口测试中心",
 *   description="比翼云 接口文档 标签命名规范：板块名称-功能模块-接口功能",
 *   @OA\Contact(name="BRICKSASP",email="649909457@qq.com",),
 * ),
 * @OA\Server(url="http://localhost:8080",description="本地开发地址",),
 * @OA\Server(url="https://shop.bricksasp.com",description="线上地址",),
 *
 *
 *
 * @OA\Schema(
 *   schema="response",
 *   title="统一返回响应数据结构",
 *   description="请求响应数据固定结构",
 *   @OA\Property(property="code",type="integer",description="接口响应状态 成功：200 失败：400"),
 *   @OA\Property(property="message",type="string",description="请求信息"),
 *   @OA\Property(property="data",type="array",description="返回数据",items={}),
 * )
 *
 *
 * @OA\Schema(
 *   schema="pagination",
 *   title="统一返回分页数据结构",
 *   description="分页数据固定结构",
 *   @OA\Property(property="pageCount",type="integer",description="分页总数"),
 *   @OA\Property(property="totalCount",type="integer",description="数据总数"),
 *   @OA\Property(property="page",type="integer",description="当前分页"),
 *   @OA\Property(property="pageSize",type="integer",description="每页行数"),
 *   @OA\Property(property="list",type="array",description="分页数据",items={}),
 * )
 * 
 * @OA\Schema(
 *   schema="queryPagination",
 *   title="统一分页请求参数",
 *   description="请求分页参数",
 *   @OA\Property(property="page",type="integer",description="当前叶数",),
 *   @OA\Property(property="pageSize",type="integer",description="每页行数",),
 * )
 * 
 * @OA\Schema(
 *   schema="province_and_city",
 *   title="统一省市区id",
 *   description="省市区id",
 *   @OA\Property(property="prov_id",type="integer",description="省份id",),
 *   @OA\Property(property="city_id",type="integer",description="城市id",),
 *   @OA\Property(property="area_id",type="integer",description="区域id",),
 * )
 */