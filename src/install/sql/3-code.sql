-- 码表

-- ----------------------------
-- Table structure for {$database_prefix}_task_type
-- 任务类型码
-- ----------------------------
drop table if exists `{$database_prefix}_task_type`;
create table `{$database_prefix}_task_type` (
    `tt_code` char(10) not null default '' comment '任务类型编码',
    `tt_name` varchar(100) not null default '' comment '任务类型别名',
    key `idx_tt_code` (`tt_code`)
) comment = '任务类型码';
-- ----------------------------
-- Table structure for `{$database_prefix}_certificate_type`
-- 证件类型
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_certificate_type`;
CREATE TABLE `{$database_prefix}_certificate_type` (
  `ct_code` char(10) NOT NULL default '' comment '证件编码',
  `ct_name` varchar(50) not null default '' comment '证件名称',
  PRIMARY KEY (`ct_code`),
  KEY `idx_ct_name` (`ct_name`) USING BTREE
) comment='证件类型';

insert into {$database_prefix}_certificate_type(ct_code,ct_name) values('11','身份证');
insert into {$database_prefix}_certificate_type(ct_code,ct_name) values('13','户口本');
insert into {$database_prefix}_certificate_type(ct_code,ct_name) values('90','军官证');
insert into {$database_prefix}_certificate_type(ct_code,ct_name) values('91','警官证');
insert into {$database_prefix}_certificate_type(ct_code,ct_name) values('92','士兵证');
insert into {$database_prefix}_certificate_type(ct_code,ct_name) values('93','国内护照');
insert into {$database_prefix}_certificate_type(ct_code,ct_name) values('94','驾照');
insert into {$database_prefix}_certificate_type(ct_code,ct_name) values('95','港澳通行证');
insert into {$database_prefix}_certificate_type(ct_code,ct_name) values('99','其他');
-- ----------------------------
-- Table structure for {$database_prefix}_country
-- 国籍
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_country`;
CREATE TABLE `{$database_prefix}_country`  (
  `c_code` char(3) NOT NULL DEFAULT '' COMMENT '编号',
  `c_title` varchar(50) NOT NULL DEFAULT '' COMMENT '名字',
  `c_py` varchar(50) NOT NULL DEFAULT '' COMMENT '拼音',
  PRIMARY KEY (`c_code`) USING BTREE
)  COMMENT = '国籍';
-- ----------------------------
-- Table structure for {$database_prefix}_nation
-- 民族
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_nation`;
CREATE TABLE `{$database_prefix}_nation`  (
  `na_code` char(2)  NOT NULL DEFAULT '',
  `na_name` varchar(20) NOT NULL DEFAULT '' COMMENT '民族',
  PRIMARY KEY (`na_code`) USING BTREE,
  INDEX `Idx_name`(`na_name`) USING BTREE
)  COMMENT = '民族';

-- ----------------------------
-- Table structure for {$database_prefix}_house_area
-- 行政区划
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_house_area`;
CREATE TABLE `{$database_prefix}_house_area`  (
  `ha_code` char(10)  NOT NULL DEFAULT '',
  `ha_name` varchar(50)  NOT NULL DEFAULT '' COMMENT '名称',
  `ha_shortname` varchar(50)  NOT NULL DEFAULT '' COMMENT '简称',
  `ha_pinyin` varchar(50)  NOT NULL DEFAULT '' COMMENT '拼音',
  `ha_pinyin_short` varchar(50)  NOT NULL DEFAULT '' COMMENT '简拼',
  `ha_citycode` varchar(50)  NOT NULL DEFAULT '' COMMENT '城市编码',
  `ha_zipcode` varchar(50)  NOT NULL DEFAULT '' COMMENT '邮政编码',
  `ha_parent` char(10)  NOT NULL DEFAULT '' COMMENT '上级地区',
  `ha_child` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否拥有下级',
  `ha_level` tinyint(4) NOT NULL DEFAULT 1 COMMENT '级别',
  `ha_path` varchar(255)  NOT NULL DEFAULT '' COMMENT '全路径',
  PRIMARY KEY (`ha_code`) USING BTREE,
  key `idx_ha_name`(`ha_name`) USING BTREE,
  key `idx_ha_pinyin`(`ha_pinyin`) USING BTREE,
  key `idx_ha_pinyin_short`(`ha_pinyin_short`) USING BTREE
)  COMMENT = '行政区划';
-- ----------------------------
-- Table structure for `{$database_prefix}_sex`
-- 性别
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_sex`;
CREATE TABLE `{$database_prefix}_sex` (
  `sex_code` char(10) NOT NULL DEFAULT '' comment '性别编码',
  `sex_name` varchar(50) NOT NULL DEFAULT '' comment '性别名称',
  PRIMARY KEY (`sex_code`),
  KEY `idx_sex_name` (`sex_name`) USING BTREE
) comment='性别类型';
INSERT INTO `{$database_prefix}_sex`(`sex_code`,`sex_name`) VALUES ('0', '未知');
INSERT INTO `{$database_prefix}_sex`(`sex_code`,`sex_name`) VALUES ('1', '男');
INSERT INTO `{$database_prefix}_sex`(`sex_code`,`sex_name`) VALUES ('2', '女');
-- ----------------------------
-- Table structure for `{$database_prefix}_relationship`
-- 人员关系
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_relationship`;
CREATE TABLE `{$database_prefix}_relationship` (
  `rs_code` char(10) NOT NULL default '' comment '关系编码',
  `rs_name` varchar(50) not null default '' comment '关系名称',
  PRIMARY KEY (`rs_code`),
  KEY `idx_rs_name` (`rs_name`) USING BTREE
) comment='人员关系';
insert into {$database_prefix}_relationship(`rs_code`,`rs_name`) values('01','父子');
insert into {$database_prefix}_relationship(`rs_code`,`rs_name`) values('02','母子');
insert into {$database_prefix}_relationship(`rs_code`,`rs_name`) values('03','父女');
insert into {$database_prefix}_relationship(`rs_code`,`rs_name`) values('04','母女');
insert into {$database_prefix}_relationship(`rs_code`,`rs_name`) values('05','妻子/丈夫');
insert into {$database_prefix}_relationship(`rs_code`,`rs_name`) values('06','朋友');
insert into {$database_prefix}_relationship(`rs_code`,`rs_name`) values('07','其它');
-- ----------------------------
-- Table structure for `{$database_prefix}_people_status`
-- 人员状态
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_people_status`;
CREATE TABLE `{$database_prefix}_people_status` (
  `ps_code` char(10) NOT NULL default '' comment '人员状态编码',
  `ps_name` varchar(50) not null default '' comment '人员状态名称',
  PRIMARY KEY (`ps_code`),
  KEY `idx_ps_name` (`ps_name`) USING BTREE
) comment='人员状态';

insert into {$database_prefix}_people_status(`ps_code`,`ps_name`) values('01','自理');
insert into {$database_prefix}_people_status(`ps_code`,`ps_name`) values('02','半自理');
insert into {$database_prefix}_people_status(`ps_code`,`ps_name`) values('03','失能');
insert into {$database_prefix}_people_status(`ps_code`,`ps_name`) values('04','失智');
insert into {$database_prefix}_people_status(`ps_code`,`ps_name`) values('05','其他');
-- ----------------------------
-- Table structure for `{$database_prefix}_culture_status`
-- 文化程度
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_culture_status`;
CREATE TABLE `{$database_prefix}_culture_status`  (
  `cs_code` char(10) NOT NULL DEFAULT '' COMMENT '编码',
  `cs_name` varchar(50)  NOT NULL DEFAULT '' COMMENT '类型',
  PRIMARY KEY (`cs_code`) USING BTREE,
  INDEX `idx_cs_name`(`cs_name`) USING BTREE
)  COMMENT = '文化程度';
INSERT INTO `{$database_prefix}_culture_status`(`cs_code`, `cs_name`) VALUES ('1', '研究生');
INSERT INTO `{$database_prefix}_culture_status`(`cs_code`, `cs_name`) VALUES ('2', '大学本科');
INSERT INTO `{$database_prefix}_culture_status`(`cs_code`, `cs_name`) VALUES ('3', '大学专科和专科学校');
INSERT INTO `{$database_prefix}_culture_status`(`cs_code`, `cs_name`) VALUES ('4', '中等专业学校');
INSERT INTO `{$database_prefix}_culture_status`(`cs_code`, `cs_name`) VALUES ('5', '技工学校');
INSERT INTO `{$database_prefix}_culture_status`(`cs_code`, `cs_name`) VALUES ('6', '高中');
INSERT INTO `{$database_prefix}_culture_status`(`cs_code`, `cs_name`) VALUES ('7', '初中');
INSERT INTO `{$database_prefix}_culture_status`(`cs_code`, `cs_name`) VALUES ('8', '小学');
INSERT INTO `{$database_prefix}_culture_status`(`cs_code`, `cs_name`) VALUES ('9', '文盲或半文盲');
INSERT INTO `{$database_prefix}_culture_status`(`cs_code`, `cs_name`) VALUES ('10', '不详');
INSERT INTO `{$database_prefix}_culture_status`(`cs_code`, `cs_name`) VALUES ('99', '其他');
-- ----------------------------
-- Table structure for `{$database_prefix}_media_channel`
-- 媒介渠道
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_media_channel`;
CREATE TABLE `{$database_prefix}_media_channel` (
  `mc_code` char(10) NOT NULL default '' comment '媒介渠道编码',
  `mc_name` varchar(50) not null default '' comment '媒介渠道名称',
  PRIMARY KEY (`mc_code`),
  KEY `idx_mc_name` (`mc_name`) USING BTREE
) comment='媒介渠道';
insert into {$database_prefix}_media_channel(`mc_code`,`mc_name`) values('01','广告');
insert into {$database_prefix}_media_channel(`mc_code`,`mc_name`) values('02','报纸');
insert into {$database_prefix}_media_channel(`mc_code`,`mc_name`) values('03','网络');
insert into {$database_prefix}_media_channel(`mc_code`,`mc_name`) values('04','朋友');
insert into {$database_prefix}_media_channel(`mc_code`,`mc_name`) values('05','小程序');
insert into {$database_prefix}_media_channel(`mc_code`,`mc_name`) values('06','公众号');
insert into {$database_prefix}_media_channel(`mc_code`,`mc_name`) values('99','其他');
-- ----------------------------
-- Table structure for {$database_prefix}_career
-- 职业
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_career`;
CREATE TABLE `{$database_prefix}_career`  (
  `ca_code` char(10) NOT NULL DEFAULT '' comment '编码',
  `ca_name` varchar(50) NOT NULL DEFAULT '' comment '名称',
  PRIMARY KEY (`ca_code`) USING BTREE,
  INDEX `idx_ca_name`(`ca_name`) USING BTREE
)  COMMENT = '职业';
INSERT INTO `{$database_prefix}_career`(`ca_code`, `ca_name`) VALUES ('11', '国家公务员');
INSERT INTO `{$database_prefix}_career`(`ca_code`, `ca_name`) VALUES ('13', '专业技术人员');
INSERT INTO `{$database_prefix}_career`(`ca_code`, `ca_name`) VALUES ('17', '职员');
INSERT INTO `{$database_prefix}_career`(`ca_code`, `ca_name`) VALUES ('21', '企业管理人员');
INSERT INTO `{$database_prefix}_career`(`ca_code`, `ca_name`) VALUES ('24', '工人');
INSERT INTO `{$database_prefix}_career`(`ca_code`, `ca_name`) VALUES ('27', '农民');
INSERT INTO `{$database_prefix}_career`(`ca_code`, `ca_name`) VALUES ('31', '学生');
INSERT INTO `{$database_prefix}_career`(`ca_code`, `ca_name`) VALUES ('37', '现役军人');
INSERT INTO `{$database_prefix}_career`(`ca_code`, `ca_name`) VALUES ('51', '自由职业者');
INSERT INTO `{$database_prefix}_career`(`ca_code`, `ca_name`) VALUES ('54', '个体经营者');
INSERT INTO `{$database_prefix}_career`(`ca_code`, `ca_name`) VALUES ('70', '无业人员');
INSERT INTO `{$database_prefix}_career`(`ca_code`, `ca_name`) VALUES ('80', '退(离)休人员');
INSERT INTO `{$database_prefix}_career`(`ca_code`, `ca_name`) VALUES ('99', '其他');
-- ----------------------------
-- Table structure for {$database_prefix}_marital_status
-- 婚姻状况
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_marital_status`;
CREATE TABLE `{$database_prefix}_marital_status`  (
  `ms_code` char(10) NOT NULL DEFAULT '' comment '编码',
  `ms_name` varchar(50) NOT NULL DEFAULT '' comment '名称',
  PRIMARY KEY (`ms_code`) USING BTREE,
  INDEX `idx_ms_name`(`ms_name`) USING BTREE
)  COMMENT = '婚姻状况';
INSERT INTO `{$database_prefix}_marital_status`(`ms_code`, `ms_name`) VALUES ('10', '未婚');
INSERT INTO `{$database_prefix}_marital_status`(`ms_code`, `ms_name`) VALUES ('20', '已婚');
INSERT INTO `{$database_prefix}_marital_status`(`ms_code`, `ms_name`) VALUES ('21', '初婚');
INSERT INTO `{$database_prefix}_marital_status`(`ms_code`, `ms_name`) VALUES ('22', '再婚');
INSERT INTO `{$database_prefix}_marital_status`(`ms_code`, `ms_name`) VALUES ('23', '复婚');
INSERT INTO `{$database_prefix}_marital_status`(`ms_code`, `ms_name`) VALUES ('30', '丧偶');
INSERT INTO `{$database_prefix}_marital_status`(`ms_code`, `ms_name`) VALUES ('40', '离婚');
INSERT INTO `{$database_prefix}_marital_status`(`ms_code`, `ms_name`) VALUES ('99', '其他');
-- ----------------------------
-- Table structure for `{$database_prefix}_stars`
-- 星级
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_stars`;
CREATE TABLE `{$database_prefix}_stars` (
  `st_code` char(10) NOT NULL COMMENT '星级编码',
  `st_name` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '星级名称',
  PRIMARY KEY (`st_code`)
)   COMMENT='星级';
insert into {$database_prefix}_stars(st_code,st_name) values('1','一星');
insert into {$database_prefix}_stars(st_code,st_name) values('2','二星');
insert into {$database_prefix}_stars(st_code,st_name) values('3','三星');
insert into {$database_prefix}_stars(st_code,st_name) values('4','四星');
insert into {$database_prefix}_stars(st_code,st_name) values('5','五星');
insert into {$database_prefix}_stars(st_code,st_name) values('9','其他');
-- ----------------------------
-- Table structure for `{$database_prefix}_grade`
-- 等级
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_grade`;
CREATE TABLE `{$database_prefix}_grade` (
  `g_code` char(10) NOT NULL COMMENT '等级编码',
  `g_name` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '等级名称',
  PRIMARY KEY (`g_code`)
)   COMMENT='等级';
insert into {$database_prefix}_grade(g_code,g_name) values('1','特级');
insert into {$database_prefix}_grade(g_code,g_name) values('2','一级');
insert into {$database_prefix}_grade(g_code,g_name) values('3','二级');
insert into {$database_prefix}_grade(g_code,g_name) values('4','三级');
insert into {$database_prefix}_grade(g_code,g_name) values('5','地方级');
insert into {$database_prefix}_grade(g_code,g_name) values('9','其他');