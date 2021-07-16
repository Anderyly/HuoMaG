/*
 Navicat Premium Data Transfer

 Source Server         : 本地
 Source Server Type    : MySQL
 Source Server Version : 50650
 Source Host           : 127.0.0.1:3306
 Source Schema         : hmgr

 Target Server Type    : MySQL
 Target Server Version : 50650
 File Encoding         : 65001

 Date: 17/03/2021 16:27:10
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ay_admin
-- ----------------------------
DROP TABLE IF EXISTS `ay_admin`;
CREATE TABLE `ay_admin` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `rid` int(11) NOT NULL DEFAULT '1' COMMENT '用户组',
  `account` varchar(255) DEFAULT NULL COMMENT '用户名',
  `password` varchar(64) DEFAULT '' COMMENT '密码',
  `nickname` varchar(50) DEFAULT '' COMMENT '呢称',
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1正常 0冻结',
  `createTime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`),
  KEY `rid` (`rid`),
  KEY `account` (`account`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='后台管理员';

-- ----------------------------
-- Records of ay_admin
-- ----------------------------
BEGIN;
INSERT INTO `ay_admin` VALUES (1, 1, 'admin', '8367877b94c6a6df423fc3b21dcc2b36', '', '', 1, 0);
COMMIT;

-- ----------------------------
-- Table structure for ay_auth_menu
-- ----------------------------
DROP TABLE IF EXISTS `ay_auth_menu`;
CREATE TABLE `ay_auth_menu` (
  `mid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pmid` int(11) unsigned DEFAULT '0' COMMENT '上级菜单id',
  `type` int(11) NOT NULL DEFAULT '1' COMMENT '几级菜单',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优先级 越小越大',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '菜单地址',
  `parameter` varchar(255) NOT NULL DEFAULT '' COMMENT '参数',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单名',
  `icon` varchar(30) NOT NULL DEFAULT '' COMMENT '菜单图标',
  `meta` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1显示 0隐藏',
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='菜单';

-- ----------------------------
-- Records of ay_auth_menu
-- ----------------------------
BEGIN;
INSERT INTO `ay_auth_menu` VALUES (1, 0, 1, 1, '#', '', '管理设置', 'fa fa-gear', 'hh', 0);
INSERT INTO `ay_auth_menu` VALUES (2, 1, 2, 2, '/admin/Account/index', '', '用户管理', '', '', 1);
INSERT INTO `ay_auth_menu` VALUES (3, 1, 2, 3, '/admin/role/index', '', '角色管理', '', '', 1);
INSERT INTO `ay_auth_menu` VALUES (4, 1, 2, 4, '/admin/menu/index', '', '菜单管理', '', '', 1);
INSERT INTO `ay_auth_menu` VALUES (5, 1, 2, 1, '/admin/config/basic', '', '基本设置', '', '', 1);
INSERT INTO `ay_auth_menu` VALUES (7, 0, 1, 1, '#', '', '活码', 'fa fa-qrcode', '', 1);
INSERT INTO `ay_auth_menu` VALUES (8, 7, 2, 0, '/admin/qrcode/index', '', '活码列表', '', '', 1);
INSERT INTO `ay_auth_menu` VALUES (9, 7, 2, 0, '/admin/qrcode1/index', '', '活码回收站', '', '', 1);
INSERT INTO `ay_auth_menu` VALUES (10, 0, 1, 0, '#', '', '统计', 'fa fa-bar-chart', '', 1);
INSERT INTO `ay_auth_menu` VALUES (11, 10, 2, 0, '/admin/chart/index', '', '日期统计', '', '', 1);
INSERT INTO `ay_auth_menu` VALUES (12, 10, 2, 0, '/admin/chart/sale', '', '员工统计', '', '', 1);
INSERT INTO `ay_auth_menu` VALUES (13, 0, 1, 0, '#', '', '员工管理', 'fa fa-user', '', 1);
INSERT INTO `ay_auth_menu` VALUES (14, 13, 2, 0, '/admin/sale/index', '', '员工列表', '', '', 1);
INSERT INTO `ay_auth_menu` VALUES (15, 13, 2, 0, '/admin/sale/add', '', '员工添加', '', '', 1);
INSERT INTO `ay_auth_menu` VALUES (16, 0, 1, 0, '#', '', '登入地址', 'fa fa-send', '', 1);
INSERT INTO `ay_auth_menu` VALUES (17, 16, 2, 0, '/admin/system/la', '', '查看登入地址', '', '', 1);
INSERT INTO `ay_auth_menu` VALUES (18, 0, 1, 0, '#', '', '系统设置', 'fa fa-bars', '', 1);
INSERT INTO `ay_auth_menu` VALUES (19, 18, 2, 0, '/admin/system/domain', '', '域名设置', '', '', 1);
INSERT INTO `ay_auth_menu` VALUES (20, 18, 2, 0, '/admin/system/set', '', '功能设置', '', '', 1);
INSERT INTO `ay_auth_menu` VALUES (21, 18, 2, 0, '/admin/system/access', '', '地区设置', '', '', 1);
INSERT INTO `ay_auth_menu` VALUES (22, 18, 2, 0, '/admin/fast/index', '', '快站设置', '', '', 1);
COMMIT;

-- ----------------------------
-- Table structure for ay_auth_role
-- ----------------------------
DROP TABLE IF EXISTS `ay_auth_role`;
CREATE TABLE `ay_auth_role` (
  `rid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色表',
  `sid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '编码id',
  `root` varchar(255) NOT NULL DEFAULT '' COMMENT '权限菜单地址',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `meta` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `createTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`rid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ay_auth_role
-- ----------------------------
BEGIN;
INSERT INTO `ay_auth_role` VALUES (1, 1, '*', '超级管理员', '', 1563351772, 1563352874);
COMMIT;

-- ----------------------------
-- Table structure for ay_config
-- ----------------------------
DROP TABLE IF EXISTS `ay_config`;
CREATE TABLE `ay_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `k` varchar(255) DEFAULT NULL,
  `v` varchar(255) DEFAULT NULL,
  `ms` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `k` (`k`)
) ENGINE=MyISAM AUTO_INCREMENT=11119 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ay_config
-- ----------------------------
BEGIN;
INSERT INTO `ay_config` VALUES (1, 'title', '微活码平台', NULL);
INSERT INTO `ay_config` VALUES (2, 'keywords', '微活码平台', NULL);
INSERT INTO `ay_config` VALUES (3, 'description', '微活码平台', NULL);
INSERT INTO `ay_config` VALUES (4, 'footer', '', NULL);
INSERT INTO `ay_config` VALUES (5, 'geet_id', '32cb1327b10356c01f652bf775dec8c3', '极验证id');
INSERT INTO `ay_config` VALUES (6, 'geet_key', '70065761478a28db7e7a34acb5960898', '极验证key');
INSERT INTO `ay_config` VALUES (7, 'yl', '4460', '域名验证次数');
INSERT INTO `ay_config` VALUES (8, 'dwz1_token', '', NULL);
INSERT INTO `ay_config` VALUES (9, 'appcode', '', 'ip识别');
INSERT INTO `ay_config` VALUES (10, 'ld', 'hmgr.cc', '落地');
INSERT INTO `ay_config` VALUES (11, 'rk', 'hmgr.cc', '入口');
INSERT INTO `ay_config` VALUES (12, 'dwz_token', '', '短网址Token');
INSERT INTO `ay_config` VALUES (13, 'security', '1', '1');
INSERT INTO `ay_config` VALUES (14, 'cache', '0', '重复添加');
INSERT INTO `ay_config` VALUES (15, 'me_domain', '0', '独立域名');
INSERT INTO `ay_config` VALUES (16, 'wechat_open', '0', '微信打开');
INSERT INTO `ay_config` VALUES (17, 'diqu_shibie', '1', NULL);
INSERT INTO `ay_config` VALUES (18, 'ip_onenum', '30', NULL);
INSERT INTO `ay_config` VALUES (19, 'area_type', '1', NULL);
INSERT INTO `ay_config` VALUES (20, 'accessfilter_status', '1', NULL);
INSERT INTO `ay_config` VALUES (21, 'check_city_word', '', NULL);
INSERT INTO `ay_config` VALUES (22, 'check_city_gotolink', 'http://baidu.com', NULL);
INSERT INTO `ay_config` VALUES (23, 'dwz_num', '5', '短网址个数');
INSERT INTO `ay_config` VALUES (100, 'dsf_url', 'hm.vclove.cn', '三方域名');
INSERT INTO `ay_config` VALUES (24, 'experience', '3', '体验时间 天');
INSERT INTO `ay_config` VALUES (26, 'jm', '0', '解码');
INSERT INTO `ay_config` VALUES (25, 'experience_tips', '<div class=\"col-md-6\"><br>本网站由 <a href=\"https://www.upyun.com/?utm_source=lianmeng&amp;utm_medium=referral\" rel=\"“nofollow”\"><img src=\"https://uss.vclove.cn/upyun.png\" style=\"width:50px;height:30px\"></a> 提供CDN/云存储服务</div>', '体验用户提示语句');
COMMIT;

-- ----------------------------
-- Table structure for hm_dwz
-- ----------------------------
DROP TABLE IF EXISTS `hm_dwz`;
CREATE TABLE `hm_dwz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `url` varchar(255) NOT NULL DEFAULT '',
  `dwz` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COMMENT='短网址';

-- ----------------------------
-- Records of hm_dwz
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for hm_pic
-- ----------------------------
DROP TABLE IF EXISTS `hm_pic`;
CREATE TABLE `hm_pic` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `meta` varchar(255) DEFAULT NULL,
  `switch` int(11) DEFAULT '0',
  `sswitch` int(11) DEFAULT '0',
  `plus` int(11) DEFAULT '0',
  `scan` int(11) DEFAULT '0',
  `createTime` int(10) DEFAULT NULL,
  `ymd` int(8) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of hm_pic
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for hm_plus
-- ----------------------------
DROP TABLE IF EXISTS `hm_plus`;
CREATE TABLE `hm_plus` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `picid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `y` varchar(4) NOT NULL,
  `m` varchar(2) NOT NULL,
  `d` varchar(2) NOT NULL,
  `createTime` int(10) NOT NULL,
  `ymd` int(8) NOT NULL,
  PRIMARY KEY (`id`,`uid`,`picid`,`pid`,`y`,`m`,`d`,`createTime`,`ymd`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of hm_plus
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for hm_product
-- ----------------------------
DROP TABLE IF EXISTS `hm_product`;
CREATE TABLE `hm_product` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL COMMENT '编码',
  `uid` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `switch` varchar(255) DEFAULT '0' COMMENT '切换次数',
  `tips` varchar(255) DEFAULT NULL COMMENT '底部提示',
  `scan` int(11) NOT NULL DEFAULT '0' COMMENT '扫描次数',
  `plus` int(11) NOT NULL DEFAULT '0' COMMENT '加好友次数',
  `pic` int(11) NOT NULL DEFAULT '0' COMMENT '图片数量',
  `createTime` int(10) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`pid`),
  KEY `code` (`code`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='产品表';

-- ----------------------------
-- Records of hm_product
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for hm_scan
-- ----------------------------
DROP TABLE IF EXISTS `hm_scan`;
CREATE TABLE `hm_scan` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `picid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `y` varchar(4) NOT NULL,
  `m` varchar(2) NOT NULL,
  `d` varchar(2) NOT NULL,
  `createTime` int(10) NOT NULL,
  `ymd` int(11) NOT NULL,
  PRIMARY KEY (`id`,`uid`,`picid`,`pid`,`y`,`m`,`d`,`createTime`,`ymd`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of hm_scan
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for hm_user
-- ----------------------------
DROP TABLE IF EXISTS `hm_user`;
CREATE TABLE `hm_user` (
  `uid` bigint(20) NOT NULL AUTO_INCREMENT,
  `account` varchar(32) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `qr` varchar(255) DEFAULT '' COMMENT '用户自定义二维码',
  `createTime` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1正常 0冻结',
  `endTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后一次登入',
  `endIp` varchar(15) DEFAULT NULL,
  `y` int(4) NOT NULL DEFAULT '0',
  `m` varchar(2) NOT NULL DEFAULT '0',
  `d` varchar(2) NOT NULL DEFAULT '0',
  `ymd` int(8) DEFAULT NULL,
  `ld` text,
  `rk` text,
  `rand` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `account` (`account`),
  KEY `y` (`y`),
  KEY `m` (`m`),
  KEY `d` (`d`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='用户';

-- ----------------------------
-- Records of hm_user
-- ----------------------------
BEGIN;
INSERT INTO `hm_user` VALUES (1, 'ceshi', '8367877b94c6a6df423fc3b21dcc2b36', '', 1614861174, 1, 0, NULL, 2021, '03', '04', 20210304, '', '', 1, NULL);
COMMIT;

-- ----------------------------
-- Table structure for hm_user_experience
-- ----------------------------
DROP TABLE IF EXISTS `hm_user_experience`;
CREATE TABLE `hm_user_experience` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `root` tinyint(1) DEFAULT '1' COMMENT '是否体验 1 是 2 否',
  `endTime1` int(10) DEFAULT NULL COMMENT '体验到期时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='用户体验表';

-- ----------------------------
-- Records of hm_user_experience
-- ----------------------------
BEGIN;
INSERT INTO `hm_user_experience` VALUES (1, 1, 1, 1514861174);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
