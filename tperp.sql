/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80012
 Source Host           : localhost:3306
 Source Schema         : tperp

 Target Server Type    : MySQL
 Target Server Version : 80012
 File Encoding         : 65001

 Date: 06/07/2020 18:22:23
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for account_cate
-- ----------------------------
DROP TABLE IF EXISTS `account_cate`;
CREATE TABLE `account_cate`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cate_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '账号分类' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of account_cate
-- ----------------------------

-- ----------------------------
-- Table structure for auth
-- ----------------------------
DROP TABLE IF EXISTS `auth`;
CREATE TABLE `auth`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色id',
  `rule_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '菜单规则id',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `role_id_idx`(`role_id`) USING BTREE,
  INDEX `rule_id_idx`(`rule_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '权限' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of auth
-- ----------------------------
INSERT INTO `auth` VALUES (10, 3, 1);
INSERT INTO `auth` VALUES (11, 3, 4);
INSERT INTO `auth` VALUES (12, 3, 6);
INSERT INTO `auth` VALUES (13, 3, 5);

-- ----------------------------
-- Table structure for menus
-- ----------------------------
DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '菜单名称',
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '路径',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '图标',
  `pid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级id',
  `is_show` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否显示：-1：不显示；1：显示；',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '菜单' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of menus
-- ----------------------------
INSERT INTO `menus` VALUES (1, '系统设置', '#setting', 'el-icon-s-tools', 0, 1, 1, 1591238313, 1594024058);
INSERT INTO `menus` VALUES (4, '菜单管理', '/menu', '', 1, 1, 3, 1591239897, 1594007224);
INSERT INTO `menus` VALUES (5, '用户管理', '/user', '', 1, 1, 7, 1591243533, 1594007257);
INSERT INTO `menus` VALUES (6, '角色管理', '/role', '', 1, 1, 4, 1594007280, 1594007287);
INSERT INTO `menus` VALUES (7, '客服订单管理', '/home', 'el-icon-tickets', 0, 1, 0, 1594007386, 1594007386);
INSERT INTO `menus` VALUES (8, '市场管理', '#market', 'el-icon-s-cooperation', 0, 1, 0, 1594022427, 1594024050);
INSERT INTO `menus` VALUES (9, '来源管理', '/source', '', 8, 1, 0, 1594023860, 1594027089);
INSERT INTO `menus` VALUES (10, '账号管理', '#account', '', 8, 1, 0, 1594027104, 1594027288);
INSERT INTO `menus` VALUES (11, '账号列表', '/account', '', 10, 1, 0, 1594027310, 1594027310);
INSERT INTO `menus` VALUES (12, '账号类型', '/account_cate', '', 10, 1, 0, 1594027361, 1594027361);

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '角色名称',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：1：开启；0：关闭；',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户角色表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES (1, '管理员', '超级管理员', 1, 1593681230, 1593684080);
INSERT INTO `roles` VALUES (3, 'test', '测试', 1, 1593937420, 1593937420);

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户密码',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '真实姓名',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：1：开启；0：关闭；',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, 'admin', '$2y$12$Hj7CtvRPb1atNyGx.ZnBbOvbBRlJZ8sgMPaahEXQJxg4BPpckGg4i', '', 1, 0, 1593942370);
INSERT INTO `user` VALUES (7, 'test1100', '$2y$10$6dGkq2iBRNUs8oAt4TRYxuBSufTOb19fScolaIvZj5JtMUYeuX9.u', 'test0101', 1, 1593937441, 1593942368);

-- ----------------------------
-- Table structure for user_role
-- ----------------------------
DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `role_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id_idx`(`user_id`) USING BTREE,
  INDEX `role_id_idx`(`role_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户角色关联' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_role
-- ----------------------------
INSERT INTO `user_role` VALUES (1, 1, 1, 0, 0);
INSERT INTO `user_role` VALUES (5, 7, 3, 0, 0);
INSERT INTO `user_role` VALUES (6, 7, 1, 0, 0);

SET FOREIGN_KEY_CHECKS = 1;
