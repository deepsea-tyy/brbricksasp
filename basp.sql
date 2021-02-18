/*
 Navicat Premium Data Transfer

 Source Server         : bricksasp
 Source Server Type    : MySQL
 Source Server Version : 50731
 Source Host           : 120.79.223.217:3307
 Source Schema         : basp

 Target Server Type    : MySQL
 Target Server Version : 50731
 File Encoding         : 65001

 Date: 18/02/2021 20:54:34
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for basp_addon
-- ----------------------------
DROP TABLE IF EXISTS `basp_addon`;
CREATE TABLE `basp_addon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `author` varchar(16) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL COMMENT '插件名称',
  `namespace` varchar(32) DEFAULT NULL COMMENT '命名空间',
  `version` varchar(16) DEFAULT NULL,
  `desc` varchar(255) DEFAULT NULL COMMENT '插件说明',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_addon_install
-- ----------------------------
DROP TABLE IF EXISTS `basp_addon_install`;
CREATE TABLE `basp_addon_install` (
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `addon_id` int(11) DEFAULT NULL,
  `install` tinyint(1) DEFAULT NULL COMMENT '0卸载1安装',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='插件安装';

-- ----------------------------
-- Table structure for basp_advert
-- ----------------------------
DROP TABLE IF EXISTS `basp_advert`;
CREATE TABLE `basp_advert` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '1:文章 2:url 3商品 4商铺',
  `start_at` int(11) DEFAULT NULL,
  `end_at` int(11) DEFAULT NULL,
  `is_delete` int(11) DEFAULT NULL COMMENT '0正常1删除',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COMMENT='广告表';

-- ----------------------------
-- Table structure for basp_advert_position
-- ----------------------------
DROP TABLE IF EXISTS `basp_advert_position`;
CREATE TABLE `basp_advert_position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL COMMENT '幻灯片名称',
  `code` varchar(32) DEFAULT NULL COMMENT '广告位置编码',
  `is_delete` tinyint(1) DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `code` (`code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COMMENT='广告位置表';

-- ----------------------------
-- Table structure for basp_advert_relation
-- ----------------------------
DROP TABLE IF EXISTS `basp_advert_relation`;
CREATE TABLE `basp_advert_relation` (
  `position_id` int(11) DEFAULT NULL,
  `advert_id` int(11) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_article
-- ----------------------------
DROP TABLE IF EXISTS `basp_article`;
CREATE TABLE `basp_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '发表者id',
  `owner_id` int(11) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `cat_id` int(11) DEFAULT NULL COMMENT '分类id',
  `keywords` varchar(255) DEFAULT NULL COMMENT 'seo keywords',
  `reprint_info` varchar(150) DEFAULT NULL COMMENT '转载文章的来源说明',
  `title` varchar(255) DEFAULT NULL COMMENT '文章标题',
  `subtitle` varchar(255) DEFAULT NULL COMMENT '副标题',
  `author` varchar(16) DEFAULT NULL COMMENT '作者',
  `image` varchar(255) DEFAULT NULL COMMENT '封面',
  `brief` text COMMENT '文章摘要',
  `content` text COMMENT '文章内容',
  `parent_id` int(11) DEFAULT NULL COMMENT '文章的父级文章 id,表示文章层级关系',
  `type` tinyint(1) DEFAULT NULL COMMENT '文章类型，1文章,2页面',
  `comments_num` int(11) DEFAULT NULL COMMENT '评论数',
  `view_num` int(11) DEFAULT NULL COMMENT '浏览数',
  `like_num` int(11) DEFAULT NULL COMMENT '文章赞数',
  `is_comment` tinyint(1) DEFAULT NULL COMMENT '评论1允许，2不允许',
  `is_top` tinyint(1) DEFAULT NULL COMMENT '1置顶 2不置顶',
  `is_recommend` tinyint(1) DEFAULT NULL COMMENT '推荐 1推荐 2不推荐',
  `release_at` int(11) DEFAULT NULL COMMENT '文章发布日期',
  `status` tinyint(1) DEFAULT NULL COMMENT '1正常 2未通过审核',
  `is_delete` tinyint(1) DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL COMMENT '文章更新时间，可在前台修改，显示给用户',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COMMENT='文章表';

-- ----------------------------
-- Table structure for basp_article_category
-- ----------------------------
DROP TABLE IF EXISTS `basp_article_category`;
CREATE TABLE `basp_article_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `sort` tinyint(6) DEFAULT NULL,
  `image_id` varchar(64) DEFAULT NULL,
  `code` char(32) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `code` (`code`) USING BTREE,
  KEY `user_id,owner_id` (`user_id`,`owner_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_article_user_log
-- ----------------------------
DROP TABLE IF EXISTS `basp_article_user_log`;
CREATE TABLE `basp_article_user_log` (
  `user_id` int(11) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户浏览记录';

-- ----------------------------
-- Table structure for basp_auth_assignment
-- ----------------------------
DROP TABLE IF EXISTS `basp_auth_assignment`;
CREATE TABLE `basp_auth_assignment` (
  `item_name` varchar(64) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`) USING BTREE,
  KEY `auth_assignment_user_id_idx` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_auth_item
-- ----------------------------
DROP TABLE IF EXISTS `basp_auth_item`;
CREATE TABLE `basp_auth_item` (
  `name` varchar(64) NOT NULL,
  `type` smallint(6) NOT NULL,
  `description` text,
  `rule_name` varchar(64) DEFAULT NULL,
  `data` blob,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_auth_item_child
-- ----------------------------
DROP TABLE IF EXISTS `basp_auth_item_child`;
CREATE TABLE `basp_auth_item_child` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL,
  PRIMARY KEY (`parent`,`child`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `basp_auth_rule`;
CREATE TABLE `basp_auth_rule` (
  `name` varchar(64) NOT NULL,
  `data` blob,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_brand
-- ----------------------------
DROP TABLE IF EXISTS `basp_brand`;
CREATE TABLE `basp_brand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `sort` int(6) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_company
-- ----------------------------
DROP TABLE IF EXISTS `basp_company`;
CREATE TABLE `basp_company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT '公司名称',
  `website` varchar(255) DEFAULT NULL COMMENT '官网',
  `logo` varchar(255) DEFAULT NULL,
  `gps` varchar(64) DEFAULT NULL COMMENT 'json格式1百度2高德3腾讯4百度',
  `level` smallint(1) DEFAULT NULL COMMENT '0默认等级',
  `mark` varchar(255) DEFAULT NULL,
  `area_id` int(11) DEFAULT NULL,
  `address` varchar(64) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL COMMENT '0待审核1审核通过2审核不能过',
  `review_at` int(11) DEFAULT NULL,
  `review_user_id` int(11) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT '0',
  `body` tinyint(1) DEFAULT NULL COMMENT '主体1境内企业',
  `main_products` text COMMENT '主营商品',
  `main_model` tinyint(1) DEFAULT NULL COMMENT '主营模式1生产厂家',
  `director` varchar(4) DEFAULT NULL COMMENT '负责人',
  `director_mail` varchar(32) DEFAULT NULL COMMENT '负责人邮箱',
  `director_mobile` char(20) DEFAULT NULL COMMENT '负责人手机',
  `operations_contact` varchar(4) DEFAULT NULL COMMENT '运营联系人',
  `operations_contact_mobile` char(20) DEFAULT NULL COMMENT '运营联系人手机',
  `operations_contact_mail` varchar(32) DEFAULT NULL COMMENT '运营联系人邮箱',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COMMENT='公司表';

-- ----------------------------
-- Table structure for basp_company_qualifications
-- ----------------------------
DROP TABLE IF EXISTS `basp_company_qualifications`;
CREATE TABLE `basp_company_qualifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL COMMENT '企业类型1有限责任公司',
  `full_address` varchar(255) DEFAULT NULL,
  `incorporation_at` varchar(16) DEFAULT NULL COMMENT '公司成立日期',
  `registered_capital` decimal(10,2) DEFAULT NULL COMMENT '注册资本',
  `business_scope` text COMMENT '经营范围',
  `credit_code` char(30) DEFAULT NULL COMMENT '社会统一信用代码',
  `business_license` char(64) DEFAULT NULL COMMENT '营业执照扫描件',
  `business_license_duration` char(32) DEFAULT NULL COMMENT '开始时间戳,结束时间戳',
  `three_to_one` tinyint(1) DEFAULT NULL COMMENT '1三证合一',
  `tax_type` tinyint(1) DEFAULT NULL COMMENT '1小规模纳税人',
  `legal_rep` varchar(4) DEFAULT NULL COMMENT '法人代表',
  `legal_rep_frontal_photo` char(64) DEFAULT NULL COMMENT '法人身份证正面照',
  `legal_rep_reverse_photo` char(64) DEFAULT NULL COMMENT '法人身份证反面照',
  `trademark_name` varchar(32) DEFAULT NULL COMMENT '商标名称',
  `trademark_license` char(64) DEFAULT NULL COMMENT '商标证书',
  `trademark_license_duration` char(32) DEFAULT NULL COMMENT '商标有效时间',
  `trademark_license_change` char(64) DEFAULT NULL COMMENT '商标变更/续展证明',
  `brand_permit` char(64) DEFAULT NULL COMMENT '品牌授权书',
  `purchase_sale_voucher` char(64) DEFAULT NULL COMMENT '购销凭证',
  `administrative_license` char(64) DEFAULT NULL COMMENT '行政许可证',
  `trademark_inspection_report` char(64) DEFAULT NULL COMMENT '检查报告',
  `trademark_inspection_report_duration` char(32) DEFAULT NULL,
  `food_business_license` char(64) DEFAULT NULL COMMENT '食品经营许可证',
  `food_business_license_duration` char(32) DEFAULT NULL,
  `food_inspection_report` char(64) DEFAULT NULL COMMENT '食品检测报告',
  `food_inspection_report_duration` char(32) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL COMMENT '1审核通过',
  `is_delete` tinyint(1) DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='公司资质表';

-- ----------------------------
-- Table structure for basp_file
-- ----------------------------
DROP TABLE IF EXISTS `basp_file`;
CREATE TABLE `basp_file` (
  `id` char(64) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `photo_width` int(11) DEFAULT NULL,
  `photo_hight` int(11) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `mime` char(255) DEFAULT NULL,
  `ext` char(32) DEFAULT NULL,
  `is_delete` int(11) DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件表';

-- ----------------------------
-- Table structure for basp_file_relation
-- ----------------------------
DROP TABLE IF EXISTS `basp_file_relation`;
CREATE TABLE `basp_file_relation` (
  `object_id` int(11) DEFAULT NULL,
  `file_id` char(64) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '1商品图片2单品图片3商品评论图片',
  `sort` tinyint(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件关联表';

-- ----------------------------
-- Table structure for basp_goods
-- ----------------------------
DROP TABLE IF EXISTS `basp_goods`;
CREATE TABLE `basp_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `version` tinyint(3) DEFAULT NULL,
  `code` char(30) DEFAULT NULL COMMENT '商品编码',
  `barcode` char(30) DEFAULT NULL COMMENT '商品条码',
  `name` varchar(255) DEFAULT NULL COMMENT '商品名称',
  `subtitle` varchar(255) NOT NULL COMMENT '副标题',
  `subtitle_short` varchar(32) DEFAULT NULL COMMENT '短标题',
  `brief` varchar(255) DEFAULT NULL COMMENT '简介',
  `keywords` varchar(255) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL COMMENT '品牌',
  `cat_id` int(11) DEFAULT NULL COMMENT '分类',
  `spec_id` int(11) DEFAULT NULL COMMENT '启用规格/规格id',
  `type` tinyint(1) DEFAULT NULL COMMENT '1实体商品2虚拟商品3虚拟物品4批发商品5计次/时商品',
  `image_id` char(64) DEFAULT NULL COMMENT '封面图',
  `video` char(64) DEFAULT NULL COMMENT '视频',
  `content` text,
  `specs` text,
  `params` text,
  `sort` int(11) DEFAULT NULL,
  `is_hot` tinyint(1) DEFAULT NULL,
  `is_recommend` tinyint(1) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT '0',
  `check_stock` tinyint(1) DEFAULT NULL COMMENT '1拍下减库存2付款减库存3不减库存',
  `show_stock` tinyint(1) DEFAULT NULL COMMENT '1显示库存',
  `pre_sale` tinyint(1) DEFAULT NULL COMMENT '1预售',
  `on_shelves` tinyint(1) DEFAULT NULL COMMENT '0未上架1上架2下架',
  `on_shelves_at` int(11) DEFAULT NULL COMMENT '上架时间',
  `off_shelves_at` int(11) DEFAULT NULL COMMENT '下架时间',
  `sell_num` int(11) DEFAULT NULL COMMENT '已售数量',
  `buy_num` int(11) DEFAULT NULL COMMENT '购买数',
  `show_buy_num` tinyint(1) DEFAULT NULL COMMENT '1显示购买数量',
  `return_num` int(11) DEFAULT NULL COMMENT '退货数量',
  `view_num` int(11) DEFAULT NULL COMMENT '浏览数',
  `comments_num` int(11) DEFAULT NULL COMMENT '评论数',
  `stock_unit` varchar(8) DEFAULT NULL COMMENT '库存单位',
  `weight_unit` varchar(8) DEFAULT NULL,
  `volume_unit` varchar(8) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL COMMENT '售价',
  `costprice` decimal(10,2) DEFAULT NULL COMMENT '成本',
  `mktprice` decimal(10,2) DEFAULT NULL COMMENT '市场价',
  `distprice` double(10,2) DEFAULT NULL COMMENT '分销价格',
  `is_vip_discount` tinyint(1) DEFAULT NULL COMMENT '1参与会员折扣',
  `vip_discount` char(8) DEFAULT NULL COMMENT '折扣0.1-10',
  `vip_price` double(10,2) DEFAULT NULL COMMENT '会员价格',
  `vip` tinyint(3) DEFAULT NULL COMMENT '会员限购等级',
  `share_title` varchar(64) DEFAULT NULL COMMENT '分享标题',
  `share_image_id` char(64) DEFAULT NULL COMMENT '分享图片',
  `share_desc` varchar(255) DEFAULT NULL COMMENT '分享描述',
  `follow_force` tinyint(1) DEFAULT NULL COMMENT '1购买强制关注',
  `follow_guide` varchar(255) DEFAULT NULL COMMENT '关注引导 跳转链接',
  `follow_tip` varchar(64) DEFAULT NULL COMMENT '未关注提示',
  `offline_write_off` tinyint(1) DEFAULT NULL COMMENT '线下核销1支持',
  `store_force` tinyint(1) DEFAULT NULL COMMENT '强制选择核销门店1是',
  `store_id` varchar(255) DEFAULT NULL COMMENT '门店id',
  `write_off_at` varchar(32) DEFAULT NULL COMMENT '兑换限时',
  `write_off_at_type` tinyint(1) DEFAULT NULL COMMENT '兑换限时类型1指定天数2指定日期',
  `postage_free_num` int(11) DEFAULT NULL COMMENT '单品满件包邮',
  `postage_free_price` decimal(10,2) DEFAULT NULL COMMENT '单品满额包邮',
  `exclude_area` varchar(255) DEFAULT NULL COMMENT '不参与单品包邮地区',
  `postage_id` int(11) DEFAULT NULL COMMENT '运费模版id',
  `postage_price` decimal(10,2) DEFAULT NULL COMMENT '统一邮费',
  `place_delivery` int(11) DEFAULT NULL COMMENT '发货地areaid',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_goods_attr
-- ----------------------------
DROP TABLE IF EXISTS `basp_goods_attr`;
CREATE TABLE `basp_goods_attr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT '0',
  `value` text,
  `type` tinyint(1) DEFAULT NULL COMMENT '1属性2参数',
  `text_type` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_goods_category
-- ----------------------------
DROP TABLE IF EXISTS `basp_goods_category`;
CREATE TABLE `basp_goods_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `sort` tinyint(5) DEFAULT NULL,
  `image_id` char(64) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT '0',
  `owner_id` int(11) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_goods_comment
-- ----------------------------
DROP TABLE IF EXISTS `basp_goods_comment`;
CREATE TABLE `basp_goods_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `goods_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `score` tinyint(1) DEFAULT NULL COMMENT '评价1-5星',
  `content` text COMMENT '评价内容',
  `seller_content` text COMMENT '商家回复',
  `status` tinyint(3) DEFAULT NULL COMMENT '1显示 2不显示',
  `is_delete` tinyint(1) DEFAULT '0',
  `like` int(11) DEFAULT NULL COMMENT '点赞数',
  `reply_num` int(11) DEFAULT NULL COMMENT '回复数',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `goods_id` (`goods_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COMMENT='商品评价表';

-- ----------------------------
-- Table structure for basp_goods_label
-- ----------------------------
DROP TABLE IF EXISTS `basp_goods_label`;
CREATE TABLE `basp_goods_label` (
  `goods_id` int(11) DEFAULT NULL,
  `lable_id` int(11) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_goods_product
-- ----------------------------
DROP TABLE IF EXISTS `basp_goods_product`;
CREATE TABLE `basp_goods_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `code` char(30) DEFAULT NULL COMMENT '商品编码',
  `barcode` char(64) DEFAULT NULL COMMENT '条形码',
  `spec` varchar(255) DEFAULT NULL,
  `on_shelves` tinyint(1) DEFAULT NULL COMMENT '1上架',
  `stock` int(11) DEFAULT NULL COMMENT '库存',
  `freeze_stock` int(11) DEFAULT NULL COMMENT '冻结库存',
  `is_default` tinyint(1) DEFAULT NULL COMMENT '1默认展示',
  `price` decimal(10,2) DEFAULT NULL COMMENT '售价',
  `costprice` decimal(10,2) DEFAULT NULL COMMENT '成本价',
  `mktprice` decimal(10,2) DEFAULT NULL COMMENT '市场价',
  `distprice` decimal(10,2) DEFAULT NULL COMMENT '分销价',
  `vip_price` decimal(10,2) DEFAULT NULL COMMENT '会员价',
  `is_vip_discount` int(11) DEFAULT NULL COMMENT '1参与会员折扣',
  `vip_discount` char(8) DEFAULT NULL COMMENT '折扣0.1-10',
  `vip` int(11) DEFAULT NULL COMMENT '限购会员等级',
  `weight` decimal(10,2) DEFAULT NULL,
  `volume` decimal(10,2) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '1正常2删除',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_goods_spec
-- ----------------------------
DROP TABLE IF EXISTS `basp_goods_spec`;
CREATE TABLE `basp_goods_spec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `attr_id` varchar(255) DEFAULT NULL,
  `param_id` varchar(255) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_label
-- ----------------------------
DROP TABLE IF EXISTS `basp_label`;
CREATE TABLE `basp_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `name` varchar(16) DEFAULT NULL,
  `style` varchar(64) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '1样式名2原生',
  `version` int(11) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id,owner_id` (`user_id`,`owner_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_label_relation
-- ----------------------------
DROP TABLE IF EXISTS `basp_label_relation`;
CREATE TABLE `basp_label_relation` (
  `object_id` int(11) DEFAULT NULL,
  `label_id` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '1文章标签2商品标签',
  `sort` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_logistics_company
-- ----------------------------
DROP TABLE IF EXISTS `basp_logistics_company`;
CREATE TABLE `basp_logistics_company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL COMMENT '公司名称',
  `code` varchar(64) DEFAULT NULL COMMENT '公司编码',
  `sort` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `logi_code` (`code`) USING BTREE,
  KEY `sort` (`sort`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2100 DEFAULT CHARSET=utf8mb4 COMMENT='物流公司';

-- ----------------------------
-- Table structure for basp_menu
-- ----------------------------
DROP TABLE IF EXISTS `basp_menu`;
CREATE TABLE `basp_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `route` varchar(256) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `data` blob,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_migration
-- ----------------------------
DROP TABLE IF EXISTS `basp_migration`;
CREATE TABLE `basp_migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_mini
-- ----------------------------
DROP TABLE IF EXISTS `basp_mini`;
CREATE TABLE `basp_mini` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `platform` tinyint(1) DEFAULT NULL COMMENT '1微信2支付宝3抖音',
  `appid` char(64) DEFAULT NULL,
  `app_secret` char(64) DEFAULT NULL COMMENT '开发密钥',
  `app_original_id` char(32) DEFAULT NULL COMMENT '原始id',
  `encoding_key` char(64) DEFAULT NULL COMMENT '消息加密密钥',
  `type` tinyint(1) DEFAULT NULL COMMENT '微信1小程序2服务号3订阅号',
  `avatar` char(64) DEFAULT NULL,
  `name` varchar(16) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT '0',
  `scene` tinyint(1) DEFAULT NULL COMMENT '场景1默认',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='小程序/公众号';

-- ----------------------------
-- Table structure for basp_nav
-- ----------------------------
DROP TABLE IF EXISTS `basp_nav`;
CREATE TABLE `basp_nav` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) DEFAULT NULL COMMENT '调用代码',
  `name` varchar(32) DEFAULT NULL COMMENT '名称',
  `remark` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) DEFAULT NULL COMMENT '1启用0关闭',
  `user_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='前台导航位置表';

-- ----------------------------
-- Table structure for basp_nav_menu
-- ----------------------------
DROP TABLE IF EXISTS `basp_nav_menu`;
CREATE TABLE `basp_nav_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nav_id` int(11) DEFAULT NULL COMMENT '导航 id',
  `parent_id` int(11) DEFAULT NULL COMMENT '父 id',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态;1:显示;0:隐藏',
  `sort` int(4) DEFAULT NULL COMMENT '排序',
  `name` varchar(64) DEFAULT NULL COMMENT '菜单名称',
  `target` varchar(16) DEFAULT NULL COMMENT '打开方式',
  `href` varchar(255) DEFAULT NULL COMMENT '链接',
  `icon` varchar(16) DEFAULT NULL COMMENT '图标',
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='前台导航菜单表';

-- ----------------------------
-- Table structure for basp_order
-- ----------------------------
DROP TABLE IF EXISTS `basp_order`;
CREATE TABLE `basp_order` (
  `id` bigint(20) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL COMMENT '订单总价',
  `pay_price` decimal(10,2) DEFAULT NULL COMMENT '支付价格',
  `pay_status` tinyint(1) DEFAULT NULL COMMENT '支付状态1未付款2已付款3部分付款4部分退款5已退款',
  `payed_price` decimal(10,2) DEFAULT NULL COMMENT '已付金额',
  `pay_platform` char(8) DEFAULT NULL COMMENT '支付方式',
  `pay_at` int(11) DEFAULT NULL COMMENT '支付时间',
  `logistics_name` varchar(32) DEFAULT NULL COMMENT '配送方式名称',
  `logistics_price` decimal(6,2) DEFAULT NULL COMMENT '配送费用',
  `logistics_id` char(30) DEFAULT NULL COMMENT '物流号',
  `seller_id` int(11) DEFAULT NULL COMMENT '店铺id',
  `complete` tinyint(1) DEFAULT NULL COMMENT '确单状态0未确认收货1确认收货',
  `complete_at` int(11) DEFAULT NULL,
  `confirm` tinyint(1) DEFAULT NULL COMMENT '确单状态发货前0未确认确单1确认确单',
  `confirm_at` int(11) DEFAULT NULL COMMENT '确认订单时间',
  `store_id` int(11) DEFAULT NULL COMMENT '自提门店ID',
  `ship_status` tinyint(1) DEFAULT NULL COMMENT '发货状态1未发货2已发货3部分发货4部分退货5已退货',
  `ship_id` int(11) DEFAULT NULL COMMENT '收货地址ID',
  `ship_area_id` int(11) DEFAULT NULL COMMENT '收货地区ID',
  `ship_address` varchar(128) DEFAULT NULL COMMENT '收货详细地址',
  `ship_name` varchar(16) DEFAULT NULL COMMENT '收货人姓名',
  `ship_phone` char(16) DEFAULT NULL COMMENT '收货电话',
  `total_weight` decimal(10,2) DEFAULT NULL COMMENT '商品总重量',
  `total_volume` decimal(10,2) DEFAULT NULL COMMENT '商品总体积',
  `tax_type` tinyint(1) DEFAULT NULL COMMENT '是否开发票1个人发票2公司发票',
  `tax_content` varchar(255) DEFAULT NULL COMMENT '发票内容',
  `type` tinyint(5) DEFAULT NULL COMMENT '订单类型1默认2其他订单',
  `tax_code` char(64) DEFAULT NULL COMMENT '税号',
  `tax_title` varchar(64) DEFAULT NULL COMMENT '发票抬头',
  `point` int(10) DEFAULT NULL COMMENT '使用积分',
  `point_money` decimal(10,2) DEFAULT NULL COMMENT '积分抵扣',
  `promotion_info` varchar(255) DEFAULT NULL COMMENT '优惠信息',
  `order_pmt` decimal(10,2) DEFAULT NULL COMMENT '订单优惠金额',
  `coupon` varchar(255) DEFAULT NULL COMMENT '优惠券信息',
  `memo` varchar(255) DEFAULT NULL COMMENT '买家备注',
  `ip` varchar(64) DEFAULT NULL COMMENT '下单IP',
  `mark` varchar(255) DEFAULT NULL COMMENT '卖家备注',
  `source` tinyint(3) DEFAULT NULL COMMENT '订单来源1pc 2wechat',
  `status` tinyint(1) DEFAULT NULL COMMENT '1正常2完成3取消4删除',
  `is_comment` tinyint(1) DEFAULT NULL COMMENT '1已评论',
  `is_delete` tinyint(1) DEFAULT '0',
  `lat` char(16) DEFAULT NULL,
  `lon` char(16) DEFAULT NULL,
  `receiver` int(11) DEFAULT NULL COMMENT '接单人',
  `receiver_at` int(11) DEFAULT NULL COMMENT '接单时间',
  `transit` int(11) DEFAULT NULL COMMENT '中转人',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单表';

-- ----------------------------
-- Table structure for basp_order_item
-- ----------------------------
DROP TABLE IF EXISTS `basp_order_item`;
CREATE TABLE `basp_order_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL COMMENT '拆单归属',
  `order_id` bigint(20) DEFAULT NULL,
  `goods_id` int(10) DEFAULT NULL,
  `product_id` int(10) DEFAULT NULL,
  `code` char(8) DEFAULT NULL COMMENT '核销码',
  `name` varchar(32) DEFAULT NULL COMMENT '商品名称',
  `spec` varchar(255) DEFAULT NULL,
  `barcode` varchar(30) DEFAULT NULL COMMENT '商品条码',
  `brief` varchar(255) DEFAULT NULL COMMENT '商品简介',
  `price` decimal(10,2) DEFAULT NULL COMMENT '售价',
  `costprice` decimal(10,2) DEFAULT NULL COMMENT '单品成本价单价',
  `mktprice` decimal(10,2) DEFAULT NULL COMMENT '单品市场价',
  `image_id` char(64) DEFAULT NULL COMMENT '图片',
  `num` int(11) DEFAULT NULL COMMENT '数量',
  `pay_price` decimal(10,2) DEFAULT NULL COMMENT '支付总金额',
  `pmt_price` decimal(10,2) DEFAULT NULL COMMENT '优惠总金额',
  `weight` decimal(10,2) DEFAULT NULL COMMENT '总重量',
  `volume` decimal(10,2) DEFAULT NULL COMMENT '总体积',
  `delivery_num` smallint(5) DEFAULT NULL COMMENT '交货数量',
  `ship_area_id` int(11) DEFAULT NULL COMMENT '收货地区ID',
  `ship_address` varchar(128) DEFAULT NULL COMMENT '收货详细地址',
  `ship_name` varchar(16) DEFAULT NULL COMMENT '收货人姓名',
  `ship_phone` varchar(16) DEFAULT NULL COMMENT '收货电话',
  `logistics_name` varchar(32) DEFAULT NULL COMMENT '配送方式名称',
  `logistics_id` char(30) DEFAULT NULL COMMENT '物流号',
  `is_comment` tinyint(1) DEFAULT NULL COMMENT '1已评论',
  `comment_at` int(11) DEFAULT NULL COMMENT '评论时间',
  `is_receive` tinyint(1) DEFAULT NULL COMMENT '1已收货',
  `receive_at` int(11) DEFAULT NULL,
  `is_exchange` tinyint(1) DEFAULT NULL COMMENT '1已换货',
  `exchange_at` int(11) DEFAULT NULL,
  `is_return` tinyint(1) DEFAULT NULL COMMENT '1已退货',
  `return_at` int(11) DEFAULT NULL,
  `confirm` tinyint(1) DEFAULT NULL COMMENT '确单状态1确认',
  `confirm_at` int(11) DEFAULT NULL COMMENT '确认时间',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COMMENT='订单明细表';

-- ----------------------------
-- Table structure for basp_order_pay
-- ----------------------------
DROP TABLE IF EXISTS `basp_order_pay`;
CREATE TABLE `basp_order_pay` (
  `id` bigint(20) NOT NULL COMMENT '支付单号 第三方平台交易流水号',
  `order_id` bigint(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '1一次性支付 2部分支付',
  `status` tinyint(1) DEFAULT NULL COMMENT '1支付成功2其他',
  `money` decimal(10,2) DEFAULT NULL COMMENT '支付金额',
  `pay_type` char(8) DEFAULT NULL COMMENT '支付类型编码',
  `pay_info` text COMMENT '回调原始参数',
  `ip` char(50) DEFAULT NULL,
  `third_id` char(64) DEFAULT NULL COMMENT '三方流水号',
  `created_at` int(1) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付单表';

-- ----------------------------
-- Table structure for basp_order_runerrands
-- ----------------------------
DROP TABLE IF EXISTS `basp_order_runerrands`;
CREATE TABLE `basp_order_runerrands` (
  `order_id` bigint(20) DEFAULT NULL,
  `content` text,
  `start_place` varchar(128) DEFAULT NULL COMMENT '起始地',
  `end_place` varchar(128) DEFAULT NULL COMMENT '目的地',
  `time` int(11) DEFAULT NULL COMMENT '办事时间',
  `weight` tinyint(2) DEFAULT NULL COMMENT '重量',
  `gender` tinyint(1) DEFAULT NULL,
  `overtime` tinyint(2) DEFAULT NULL COMMENT '超时 小时',
  `tip` decimal(10,2) DEFAULT NULL COMMENT '小费',
  `samount` decimal(10,2) DEFAULT NULL COMMENT '代购金额',
  `school_id` int(11) DEFAULT NULL,
  `school_area_id` int(11) DEFAULT NULL,
  `phone` char(16) DEFAULT NULL,
  UNIQUE KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='跑腿';

-- ----------------------------
-- Table structure for basp_order_runerrands_cancel
-- ----------------------------
DROP TABLE IF EXISTS `basp_order_runerrands_cancel`;
CREATE TABLE `basp_order_runerrands_cancel` (
  `order_id` bigint(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_order_setting
-- ----------------------------
DROP TABLE IF EXISTS `basp_order_setting`;
CREATE TABLE `basp_order_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `order_duration` tinyint(2) DEFAULT NULL COMMENT '订单有效期天数',
  `return_duration` tinyint(2) DEFAULT NULL COMMENT '退货有效天数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_order_settle
-- ----------------------------
DROP TABLE IF EXISTS `basp_order_settle`;
CREATE TABLE `basp_order_settle` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` bigint(20) DEFAULT NULL,
  `money` decimal(10,2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='订单结算表';

-- ----------------------------
-- Table structure for basp_pay_setting
-- ----------------------------
DROP TABLE IF EXISTS `basp_pay_setting`;
CREATE TABLE `basp_pay_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `platform` int(11) DEFAULT NULL,
  `config` text,
  `status` tinyint(1) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_postage_template
-- ----------------------------
DROP TABLE IF EXISTS `basp_postage_template`;
CREATE TABLE `basp_postage_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL COMMENT '配送方式名称',
  `is_default` tinyint(1) DEFAULT NULL COMMENT '1默认',
  `billing_plan` tinyint(1) DEFAULT NULL COMMENT '1按重量计费2按件计费',
  `logistics_id` int(11) DEFAULT NULL COMMENT '物流公司id',
  `special_type` tinyint(1) DEFAULT NULL COMMENT '1不配送区域2只配送区域',
  `special_area_id` text,
  `special_status` tinyint(1) DEFAULT NULL COMMENT '1启用',
  `is_delete` tinyint(1) DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='运费模板';

-- ----------------------------
-- Table structure for basp_postage_tpl_area
-- ----------------------------
DROP TABLE IF EXISTS `basp_postage_tpl_area`;
CREATE TABLE `basp_postage_tpl_area` (
  `postage_id` int(11) DEFAULT NULL,
  `first` char(8) DEFAULT NULL COMMENT '首重/件',
  `first_price` decimal(10,2) DEFAULT NULL,
  `renew` char(8) DEFAULT NULL COMMENT '续重/件',
  `renew_price` decimal(10,2) DEFAULT NULL,
  `full_price` decimal(10,2) DEFAULT NULL COMMENT '邮费满额包邮价',
  `area_id` text,
  KEY `basp_postage_tpl_area_postage_id_index` (`postage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_promotion
-- ----------------------------
DROP TABLE IF EXISTS `basp_promotion`;
CREATE TABLE `basp_promotion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `num` int(11) DEFAULT NULL COMMENT '促销数量',
  `receive_num` int(11) DEFAULT NULL COMMENT '限领次数',
  `participant_num` int(11) DEFAULT NULL COMMENT '参与人数',
  `instruction` varchar(255) DEFAULT NULL COMMENT '使用说明',
  `scene` tinyint(1) DEFAULT NULL COMMENT '使用场景 1默认',
  `type` tinyint(1) DEFAULT NULL COMMENT '1优惠券2促销3团购4秒杀',
  `code` char(16) DEFAULT NULL COMMENT '调用代码',
  `start_at` int(11) DEFAULT NULL,
  `end_at` int(11) DEFAULT NULL COMMENT '-1:永久有效',
  `exclusion` tinyint(1) DEFAULT NULL COMMENT '排他1是2否',
  `sort` int(11) DEFAULT NULL COMMENT '排序/权重',
  `status` tinyint(1) DEFAULT NULL COMMENT '1显示',
  `is_delete` tinyint(1) DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_promotion_condition
-- ----------------------------
DROP TABLE IF EXISTS `basp_promotion_condition`;
CREATE TABLE `basp_promotion_condition` (
  `promotion_id` int(11) DEFAULT NULL,
  `condition_type` tinyint(1) DEFAULT NULL COMMENT '达成条件类型 1最少金额2最少数量',
  `condition` varchar(255) DEFAULT NULL COMMENT '达成条件',
  `content_type` tinyint(1) DEFAULT NULL COMMENT '促销类型1商品分类2指定商品3指定单品4订单满减',
  `content` varchar(255) DEFAULT NULL COMMENT '促销内容',
  `result_type` tinyint(1) DEFAULT NULL COMMENT '促销结果 1商品减固定金额2商品折扣3商品一口价4订单减固定金额5订单折扣6订单一口价',
  `result` varchar(255) DEFAULT NULL COMMENT '促销结果'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_promotion_coupon
-- ----------------------------
DROP TABLE IF EXISTS `basp_promotion_coupon`;
CREATE TABLE `basp_promotion_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `promotion_id` int(11) DEFAULT NULL,
  `code` char(8) DEFAULT NULL,
  `status` tinyint(11) DEFAULT NULL COMMENT '0未使用1已使用',
  `type` tinyint(1) DEFAULT NULL COMMENT '1领取获得2购买获得',
  `start_at` int(11) DEFAULT NULL,
  `end_at` int(11) DEFAULT NULL,
  `exclusion` tinyint(1) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_real_name_auth
-- ----------------------------
DROP TABLE IF EXISTS `basp_real_name_auth`;
CREATE TABLE `basp_real_name_auth` (
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(4) DEFAULT NULL,
  `gender` tinyint(1) DEFAULT NULL COMMENT '性别',
  `id_card_no` char(18) DEFAULT NULL COMMENT '身份证号',
  `id_card_frontal_photo` char(64) DEFAULT NULL COMMENT '身份证正面照',
  `id_card_reverse_photo` char(64) DEFAULT NULL COMMENT '身份证反面照',
  `status` tinyint(1) DEFAULT NULL COMMENT '0未审核1通过2拒绝',
  `refuse_reasons` varchar(255) DEFAULT NULL COMMENT '拒绝原因',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='实名认证';

-- ----------------------------
-- Table structure for basp_region
-- ----------------------------
DROP TABLE IF EXISTS `basp_region`;
CREATE TABLE `basp_region` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` bigint(12) DEFAULT NULL COMMENT '行政区划代码',
  `name` varchar(32) DEFAULT NULL COMMENT '名称',
  `province` varchar(32) DEFAULT NULL COMMENT '省/直辖市',
  `city` varchar(32) DEFAULT NULL COMMENT '市',
  `area` varchar(32) DEFAULT NULL COMMENT '区',
  `town` varchar(32) DEFAULT NULL COMMENT '城镇地区',
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `province` (`province`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=46121 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_relation
-- ----------------------------
DROP TABLE IF EXISTS `basp_relation`;
CREATE TABLE `basp_relation` (
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `recommend_id` int(11) DEFAULT NULL COMMENT '推荐人',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户关系表';

-- ----------------------------
-- Table structure for basp_rule
-- ----------------------------
DROP TABLE IF EXISTS `basp_rule`;
CREATE TABLE `basp_rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL COMMENT '规则名称',
  `type` tinyint(1) DEFAULT NULL,
  `rule` char(64) DEFAULT NULL COMMENT '规则文件',
  `config` text COMMENT '规则参数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_rule_relation
-- ----------------------------
DROP TABLE IF EXISTS `basp_rule_relation`;
CREATE TABLE `basp_rule_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) DEFAULT NULL COMMENT '1商品2单品',
  `object_id` int(11) DEFAULT NULL,
  `rule_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='规则关系表';

-- ----------------------------
-- Table structure for basp_runerrands_cost
-- ----------------------------
DROP TABLE IF EXISTS `basp_runerrands_cost`;
CREATE TABLE `basp_runerrands_cost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `basic_cost` decimal(10,2) DEFAULT NULL COMMENT '基础费',
  `lunch_time_cost` decimal(10,2) DEFAULT NULL COMMENT '特殊时段费',
  `dinner_time_cost` decimal(10,2) DEFAULT NULL COMMENT '特殊时段费',
  `difficulty_cost` decimal(10,2) DEFAULT NULL COMMENT '难度费5楼以上',
  `weather_cist` decimal(10,2) DEFAULT NULL COMMENT '天气费',
  `platform_perc` tinyint(2) DEFAULT NULL COMMENT '平台抽成',
  `stationmaster_perc` tinyint(2) DEFAULT NULL COMMENT '站长抽成',
  `settlement_type` tinyint(1) DEFAULT NULL COMMENT '结算方式1微信零钱2银行卡',
  `settlement_least` decimal(10,2) DEFAULT NULL COMMENT '最低结算金额',
  `settlement_date` tinyint(2) DEFAULT NULL COMMENT '结算日期',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='跑腿费用设置';

-- ----------------------------
-- Table structure for basp_runerrands_cost_weight
-- ----------------------------
DROP TABLE IF EXISTS `basp_runerrands_cost_weight`;
CREATE TABLE `basp_runerrands_cost_weight` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cost_id` int(11) DEFAULT NULL,
  `title` varchar(32) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_runerrands_rider
-- ----------------------------
DROP TABLE IF EXISTS `basp_runerrands_rider`;
CREATE TABLE `basp_runerrands_rider` (
  `user_id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL,
  `school_area_id` int(11) DEFAULT NULL,
  `name` varchar(8) DEFAULT NULL,
  `phone` char(11) DEFAULT NULL,
  `has_car` tinyint(1) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `refuse_reasons` text,
  `password` char(64) DEFAULT NULL,
  `tmp_msg` tinyint(1) DEFAULT NULL COMMENT '1订阅消息通知',
  `work_status` tinyint(1) DEFAULT NULL COMMENT '1接单中',
  `day_order` tinyint(3) DEFAULT NULL COMMENT '日单数',
  `total_order` int(11) DEFAULT NULL COMMENT '累计单数',
  `day_money` decimal(10,2) DEFAULT '0.00',
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `passa_at` int(11) DEFAULT NULL COMMENT '审核通过时间',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='骑手表';

-- ----------------------------
-- Table structure for basp_school
-- ----------------------------
DROP TABLE IF EXISTS `basp_school`;
CREATE TABLE `basp_school` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL COMMENT '学校名称',
  `parent_id` int(11) DEFAULT NULL,
  `code` char(16) DEFAULT NULL COMMENT '学校标识码',
  `level` tinyint(1) DEFAULT NULL COMMENT '1本科2专科',
  `city` varchar(32) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `logo` char(64) DEFAULT NULL,
  `mark` varchar(16) DEFAULT NULL COMMENT '1民办',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2741 DEFAULT CHARSET=utf8mb4 COMMENT='学校表';

-- ----------------------------
-- Table structure for basp_school_around
-- ----------------------------
DROP TABLE IF EXISTS `basp_school_around`;
CREATE TABLE `basp_school_around` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL COMMENT '学校id',
  `name` varchar(64) DEFAULT NULL COMMENT '地点名称',
  `logo` char(64) DEFAULT NULL,
  `address` varchar(128) DEFAULT NULL COMMENT '详细地址',
  `area_id` int(11) DEFAULT NULL,
  `type` tinyint(2) DEFAULT NULL COMMENT '1取快递2外卖代拿3跑腿',
  `lat` char(16) DEFAULT NULL,
  `lon` char(16) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='学校周边表';

-- ----------------------------
-- Table structure for basp_school_cost_setting
-- ----------------------------
DROP TABLE IF EXISTS `basp_school_cost_setting`;
CREATE TABLE `basp_school_cost_setting` (
  `school_id` int(11) DEFAULT NULL,
  `is_lunch_cost` tinyint(1) DEFAULT NULL,
  `is_dinner_cost` tinyint(1) DEFAULT NULL,
  `is_weather_cist` tinyint(1) DEFAULT NULL,
  `is_difficulty_cost` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_search_keywords
-- ----------------------------
DROP TABLE IF EXISTS `basp_search_keywords`;
CREATE TABLE `basp_search_keywords` (
  `owner_id` int(11) DEFAULT NULL,
  `keywords` varchar(128) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `num` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '1商品2文章',
  KEY `num` (`num`) USING BTREE,
  KEY `keywords` (`keywords`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='搜索关键字统计';

-- ----------------------------
-- Table structure for basp_setting
-- ----------------------------
DROP TABLE IF EXISTS `basp_setting`;
CREATE TABLE `basp_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `title` varchar(64) DEFAULT NULL,
  `key` char(64) DEFAULT NULL,
  `val` varchar(255) DEFAULT NULL,
  `val1` varchar(255) DEFAULT NULL,
  `val2` varchar(255) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '分类1基础设置2微信开放平台设置3商城设置4网站设置5版权版本设置',
  `ext` text COMMENT '拓展内容',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `owner_id` (`owner_id`,`key`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=398 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_ship_address
-- ----------------------------
DROP TABLE IF EXISTS `basp_ship_address`;
CREATE TABLE `basp_ship_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `area_id` int(11) DEFAULT NULL COMMENT '收货地区ID',
  `address` varchar(128) DEFAULT NULL COMMENT '收货详细地址',
  `name` varchar(4) DEFAULT NULL COMMENT '收货人姓名',
  `phone` char(16) DEFAULT NULL COMMENT '收货电话',
  `is_default` tinyint(1) DEFAULT NULL COMMENT '1是',
  `school` varchar(32) DEFAULT NULL COMMENT '学校名称',
  `school_area` varchar(32) DEFAULT NULL COMMENT '校区',
  `building_no` varchar(8) DEFAULT NULL COMMENT '楼号',
  `floor` varchar(8) DEFAULT NULL COMMENT '楼层',
  `house_number` tinyint(4) DEFAULT NULL COMMENT '门牌号',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COMMENT='收货地址';

-- ----------------------------
-- Table structure for basp_shopping_cart
-- ----------------------------
DROP TABLE IF EXISTS `basp_shopping_cart`;
CREATE TABLE `basp_shopping_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `num` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_shortener
-- ----------------------------
DROP TABLE IF EXISTS `basp_shortener`;
CREATE TABLE `basp_shortener` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `scene` tinyint(5) DEFAULT NULL,
  `code` char(32) DEFAULT NULL COMMENT '显示值',
  `type` int(1) DEFAULT NULL COMMENT '1商品2文章3酒卡4个人',
  `object_id` char(32) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '1使用后删除',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `key` (`code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COMMENT='短密参数表';

-- ----------------------------
-- Table structure for basp_sms
-- ----------------------------
DROP TABLE IF EXISTS `basp_sms`;
CREATE TABLE `basp_sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `mobile` bigint(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL COMMENT '使用状态0未发送1已发送2已使用',
  `content` varchar(255) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '1验证码2其他信息',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COMMENT='短消息';

-- ----------------------------
-- Table structure for basp_sms_setting
-- ----------------------------
DROP TABLE IF EXISTS `basp_sms_setting`;
CREATE TABLE `basp_sms_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `secret_id` char(64) DEFAULT NULL,
  `secret_key` char(64) DEFAULT NULL,
  `platform` tinyint(1) DEFAULT NULL COMMENT '1腾讯2阿里',
  `status` tinyint(1) DEFAULT NULL COMMENT '1启用',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='短信设置';

-- ----------------------------
-- Table structure for basp_sms_tpl
-- ----------------------------
DROP TABLE IF EXISTS `basp_sms_tpl`;
CREATE TABLE `basp_sms_tpl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `code` char(32) DEFAULT NULL COMMENT '模版标识',
  `content` varchar(255) DEFAULT NULL,
  `platform` tinyint(1) DEFAULT NULL COMMENT '1腾讯2阿里',
  `sign` char(16) DEFAULT NULL COMMENT '签名',
  `tpl_id` char(16) DEFAULT NULL COMMENT '平台模版id号',
  `appid` char(32) DEFAULT NULL,
  `is_delete` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='短信模版';

-- ----------------------------
-- Table structure for basp_store
-- ----------------------------
DROP TABLE IF EXISTS `basp_store`;
CREATE TABLE `basp_store` (
  `owner_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL COMMENT '0表示主店 其他表示分店',
  `name` varchar(32) DEFAULT NULL COMMENT '店铺名称',
  `industry_id` int(11) DEFAULT NULL COMMENT '行业分类id',
  `type` tinyint(1) DEFAULT NULL COMMENT '1商铺2供货商3学校',
  `nature` tinyint(1) DEFAULT NULL COMMENT '经营性质1旗舰店2专营店3专卖店',
  `brand` varchar(32) DEFAULT NULL COMMENT '经营品牌',
  `logo` varchar(64) DEFAULT NULL,
  `brief` varchar(255) DEFAULT NULL,
  `contacts` varchar(4) DEFAULT NULL COMMENT '联系人',
  `contacts_phone` varchar(20) DEFAULT NULL COMMENT '联系人电话',
  `return_area_id` int(11) DEFAULT NULL,
  `return_address` varchar(64) DEFAULT NULL COMMENT '退货地址',
  `tm_url` varchar(255) DEFAULT NULL COMMENT '天猫店铺链接',
  `jd_url` varchar(255) DEFAULT NULL COMMENT '京东店铺链接',
  `own_site_url` varchar(255) DEFAULT NULL COMMENT '自营网站',
  `area_id` int(11) DEFAULT NULL,
  `address` varchar(64) DEFAULT NULL COMMENT '店铺地址',
  `status` tinyint(1) DEFAULT NULL COMMENT '0未审核1通过2拒绝',
  `refuse_reasons` varchar(255) DEFAULT NULL COMMENT '拒绝原因',
  `is_delete` tinyint(1) DEFAULT '0',
  `start_at` int(11) DEFAULT NULL COMMENT '开始有效时间',
  `end_at` int(11) DEFAULT NULL COMMENT '结束有效时间',
  `total_amount` decimal(10,2) DEFAULT NULL COMMENT '总收入',
  `out_amount` decimal(10,2) DEFAULT NULL COMMENT '总提现',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商铺基本信息';

-- ----------------------------
-- Table structure for basp_store_clerk
-- ----------------------------
DROP TABLE IF EXISTS `basp_store_clerk`;
CREATE TABLE `basp_store_clerk` (
  `user_id` int(11) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '1老板2店长3店员',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_store_relation
-- ----------------------------
DROP TABLE IF EXISTS `basp_store_relation`;
CREATE TABLE `basp_store_relation` (
  `owner_id` int(11) DEFAULT NULL,
  `object_id` int(11) DEFAULT NULL,
  `type` tinyint(2) DEFAULT NULL COMMENT '1学校'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学校归属关系表';

-- ----------------------------
-- Table structure for basp_student_auth
-- ----------------------------
DROP TABLE IF EXISTS `basp_student_auth`;
CREATE TABLE `basp_student_auth` (
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `school_id` int(11) DEFAULT NULL COMMENT '主校id',
  `school_area_id` int(11) DEFAULT NULL COMMENT '校区id',
  `faculty` varchar(32) DEFAULT NULL COMMENT '院系',
  `subject` varchar(32) DEFAULT NULL COMMENT '专业',
  `enrollment_at` int(11) DEFAULT NULL COMMENT '入学时间',
  `student_id` int(11) DEFAULT NULL COMMENT '学号',
  `student_id_card_frontal_photo` char(64) DEFAULT NULL COMMENT '学生证正面照',
  `student_id_card_reverse_photo` char(64) DEFAULT NULL COMMENT '学生证反面照',
  `status` tinyint(1) DEFAULT NULL COMMENT '0未审核1通过2拒绝',
  `refuse_reasons` varchar(255) DEFAULT NULL COMMENT '拒绝原因',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学生认真信息';

-- ----------------------------
-- Table structure for basp_sys_tpl_msg
-- ----------------------------
DROP TABLE IF EXISTS `basp_sys_tpl_msg`;
CREATE TABLE `basp_sys_tpl_msg` (
  `owner_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `code` char(32) NOT NULL COMMENT '标识',
  `wx_tpl_id` char(64) DEFAULT NULL COMMENT '公众号模板id',
  `wx_tpl_jump` text COMMENT '模板消息跳转',
  `wx_content` text COMMENT '公众号模板内容',
  `wx_mini_tpl_id` char(64) DEFAULT NULL COMMENT '小程序模版id',
  `wx_mini_content` text COMMENT '小程序模板内容',
  `status` tinyint(2) DEFAULT NULL COMMENT '1小程序2公众号3全部4关闭',
  `type` tinyint(2) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`owner_id`,`code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统模版消息';

-- ----------------------------
-- Table structure for basp_user
-- ----------------------------
DROP TABLE IF EXISTS `basp_user`;
CREATE TABLE `basp_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `auth_key` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` bigint(11) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT '10',
  `access_token` varchar(64) DEFAULT NULL,
  `type` tinyint(1) DEFAULT '1' COMMENT '1会员账号2管理员账号',
  `is_delete` tinyint(1) DEFAULT '0',
  `invite_code` char(8) DEFAULT NULL COMMENT '个人邀请码',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `basp_user_username_uindex` (`username`),
  UNIQUE KEY `basp_user_password_reset_token_uindex` (`password_reset_token`),
  UNIQUE KEY `basp_user_email_uindex` (`email`),
  UNIQUE KEY `basp_user_mobile_uindex` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_user_draw_money
-- ----------------------------
DROP TABLE IF EXISTS `basp_user_draw_money`;
CREATE TABLE `basp_user_draw_money` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `money` decimal(10,2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL COMMENT '1提现成功',
  `commission` decimal(10,2) DEFAULT NULL,
  `platform` tinyint(1) DEFAULT NULL COMMENT '1微信2支付宝',
  `draw_type` tinyint(1) DEFAULT NULL COMMENT '1零钱',
  `scene` tinyint(2) DEFAULT NULL COMMENT '1跑腿',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1000000000000000016 DEFAULT CHARSET=utf8mb4 COMMENT='提现表';

-- ----------------------------
-- Table structure for basp_user_fund
-- ----------------------------
DROP TABLE IF EXISTS `basp_user_fund`;
CREATE TABLE `basp_user_fund` (
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) DEFAULT '0.00' COMMENT '可提现',
  `discount_amount` decimal(10,2) DEFAULT '0.00' COMMENT '不可提现',
  `use_amount` decimal(10,2) DEFAULT '0.00' COMMENT '已消费金额',
  `coin` decimal(10,2) DEFAULT '0.00' COMMENT '代币',
  `score` int(11) DEFAULT '0' COMMENT '可用积分',
  `exp` int(11) DEFAULT '0' COMMENT '经验值',
  `credit` int(11) DEFAULT '0' COMMENT '信用分',
  `version` int(11) DEFAULT '1',
  `out_amount` decimal(10,2) DEFAULT '0.00' COMMENT '累计提现',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户资金表';

-- ----------------------------
-- Table structure for basp_user_fund_log
-- ----------------------------
DROP TABLE IF EXISTS `basp_user_fund_log`;
CREATE TABLE `basp_user_fund_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `point` decimal(10,2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL COMMENT '1入账2出账',
  `type` tinyint(2) DEFAULT NULL COMMENT '1money2积分3信用分',
  `object_id` bigint(20) DEFAULT NULL COMMENT '来源id',
  `object_type` tinyint(2) DEFAULT NULL COMMENT '1订单2提现',
  `perc` decimal(10,2) DEFAULT NULL COMMENT '抽成',
  `scene` tinyint(5) DEFAULT NULL COMMENT '1跑腿',
  `amount` decimal(10,2) DEFAULT NULL COMMENT '账户余额',
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COMMENT='资产变动流水表';

-- ----------------------------
-- Table structure for basp_user_info
-- ----------------------------
DROP TABLE IF EXISTS `basp_user_info`;
CREATE TABLE `basp_user_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `show_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL COMMENT '姓名',
  `nickname` varchar(32) DEFAULT NULL,
  `birthday` int(11) DEFAULT NULL,
  `age` tinyint(3) DEFAULT NULL,
  `gender` tinyint(1) DEFAULT NULL,
  `last_login_ip` varchar(64) DEFAULT NULL,
  `last_login_time` int(11) DEFAULT NULL,
  `last_login_area` varchar(64) DEFAULT NULL,
  `login_count` tinyint(5) DEFAULT NULL,
  `vip` varchar(32) DEFAULT NULL COMMENT '会员等级',
  `vip_duration` int(11) DEFAULT NULL COMMENT 'vip结束时间',
  `platform` tinyint(3) DEFAULT NULL COMMENT '用户类型 1站内用户2微信用户3支付宝用户4抖音用户',
  `openid` varchar(128) DEFAULT NULL,
  `unionid` varchar(128) DEFAULT NULL,
  `level` tinyint(3) DEFAULT NULL COMMENT '级别',
  `company_id` int(11) DEFAULT NULL COMMENT '公司id',
  `uuid` varchar(128) DEFAULT NULL,
  `mark` varchar(64) DEFAULT NULL COMMENT '备注',
  `type` tinyint(1) DEFAULT NULL COMMENT '注册入口 1普通会员2商家',
  `scene` tinyint(1) DEFAULT NULL COMMENT '应用场景',
  `school_id` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for basp_user_vip
-- ----------------------------
DROP TABLE IF EXISTS `basp_user_vip`;
CREATE TABLE `basp_user_vip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `level` tinyint(4) DEFAULT NULL,
  `up_cdt` tinyint(1) DEFAULT NULL COMMENT '升级条件1订单金额/数量2指定商品',
  `up_cdt_val` char(8) DEFAULT NULL,
  `discount` char(4) DEFAULT NULL COMMENT '折扣',
  `duration` tinyint(4) DEFAULT NULL COMMENT '时间期限 月',
  `status` tinyint(1) DEFAULT NULL,
  `is_delete` int(11) DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='会员等级';

SET FOREIGN_KEY_CHECKS = 1;
