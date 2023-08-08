-- 应用业务表

-- ----------------------------
-- Table structure for {$database_prefix}_corporation
-- 集团
-- ----------------------------
drop table if exists `{$database_prefix}_corporation`;
create table `{$database_prefix}_corporation` (
    `corp_id` INT not null auto_increment comment '主键',
    `corp_code` varchar(50) not null default '' comment '集团编码',
    `corp_name` varchar(50) not null default '' comment '集团名称',
    `corp_alias` varchar(50) not null default '' comment '集团别名',
    `corp_principal` VARCHAR ( 50 ) NOT NULL DEFAULT '' COMMENT '主要负责人',
    `corp_principal_cert_type` VARCHAR(10) NOT NULL DEFAULT '11' COMMENT '主要负责人证件类型',
    `corp_principal_cert_code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '主要负责人证件编码',
    `corp_longitude` DECIMAL(28,10) NOT NULL DEFAULT 0 COMMENT '经度',
    `corp_latitude` DECIMAL(28,10) NOT NULL DEFAULT 0 COMMENT '纬度',
    `corp_address` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '地址',
    `corp_state` TINYINT NOT NULL DEFAULT 1 COMMENT '状态，0-关闭；1-开启',
    `corp_logo_url` VARCHAR (255) NOT NULL DEFAULT '' COMMENT 'logo的uri地址',
    `corp_logo_file` INT NOT NULL DEFAULT 0 COMMENT '关联file表的id',
    `corp_create_time` int not null default 0 comment '完成时间',
    `corp_update_time` int not null default 0 comment '修改时间',
    `corp_delete_time` int not null default 0 comment '删除时间',
    primary key (`corp_id`),
    key `idx_corp_code` (`corp_code`) using BTREE,
    key `idx_corp_state` (`corp_state`) using BTREE,
    key `idx_corp_delete_time` (`corp_delete_time`) using BTREE
) comment '集团';

-- ----------------------------
-- Table structure for {$database_prefix}_organization
-- 机构
-- ----------------------------
drop table if exists `{$database_prefix}_organization`;
create table `{$database_prefix}_organization` (
    `org_id` INT not null auto_increment comment '主键',
    `org_code` varchar(50) not null default '' comment '机构编码',
    `org_name` varchar(50) not null default '' comment '机构名称',
    `org_alias` varchar(50) not null default '' comment '机构别名',
    `org_principal` VARCHAR ( 50 ) NOT NULL DEFAULT '' COMMENT '主要负责人',
    `org_principal_cert_type` VARCHAR(10) NOT NULL DEFAULT '11' COMMENT '主要负责人证件类型',
    `org_principal_cert_code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '主要负责人证件编码',
    `org_longitude` DECIMAL(28,10) NOT NULL DEFAULT 0 COMMENT '经度',
    `org_latitude` DECIMAL(28,10) NOT NULL DEFAULT 0 COMMENT '纬度',
    `org_address` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '地址',
    `org_state` TINYINT NOT NULL DEFAULT 1 COMMENT '状态，0-关闭；1-开启',
    `org_logo_url` VARCHAR (255) NOT NULL DEFAULT '' COMMENT 'logo的uri地址',
    `org_logo_file` INT NOT NULL DEFAULT 0 COMMENT '关联file表的id',
    `org_create_time` int not null default 0 comment '完成时间',
    `org_update_time` int not null default 0 comment '修改时间',
    `org_delete_time` int not null default 0 comment '删除时间',
    primary key (`org_id`),
    key `idx_org_code` (`org_code`) using BTREE,
    key `idx_org_state` (`org_state`) using BTREE,
    key `idx_org_delete_time` (`org_delete_time`) using BTREE
) comment '机构';