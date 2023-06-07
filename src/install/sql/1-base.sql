-- app_type  1管理员，2服务商，3医院
-- ----------------------------
-- Table structure for `{$database_prefix}_dict`
-- 字典表
-- ----------------------------
drop table if exists `{$database_prefix}_dict`;
create table `{$database_prefix}_dict` (
    `d_id` INT not null default 0,
    `d_name` VARCHAR (50) not null default '' comment '表中文名',
    `d_tablename` VARCHAR (50) not null default '' comment '表名,不带前缀，使用驼峰命名',
    `d_sub` VARCHAR (50) not null default '' comment '子表名',
    `d_prefix` VARCHAR (10) not null default '' comment '字段前缀',
    primary key (`d_id`),
    key `idx_d_tablename` (`d_tablename`) using BTREE
) comment = '字典表';
-- ----------------------------
-- Table structure for `{$database_prefix}_dict_item`
-- 字典项表
-- ----------------------------
drop table if exists `{$database_prefix}_dict_item`;
create table `{$database_prefix}_dict_item` (
    --  基本信息
    `di_id` INT not null auto_increment,
    `di_dict` INT not null default 0 comment '表序号',
    `di_name` VARCHAR (50) not null default '' comment '字段中文名',
    `di_fieldname` VARCHAR (50) not null default '' comment '字段英文名',
    `di_type` TINYINT not null default 6 comment '字段类型,1:整数 2:小数 3:日期 4:时间 5：日期时间 6：字符串 7布尔 8长字符串 9图像数据 10二进制',
    `di_subtype` TINYINT not null default 0 comment '字段子类型',
    --
    --   1：整数       subType 0：(无) 1：颜色 2：货币
    --   2：小数    subType 0：(无) 2：货币
    --   3,4,5 日期时间 subType 0:数据库原生时间日期类型 1:Unix时间戳   2:字符串形式 ,具体字符串格式写入di_select中
    --   6：字符串 subtype 0:(无) 1:电话号码   2：手机号码  3：邮政编码  4：电子邮件 5:拼音简码
    --   8: 字符 subtype 0：无（长字符串），1：json
    --   9：图像数据  subtype 0:无(视为二进制流) 1：路径  2：base64
    --   10：二进制数据  subtype 0:无 1：路径   2:base64
    --
    `di_max` FLOAT not null default - 1 comment '字段最大值',
    `di_min` FLOAT not null default 0 comment '字段最小值，当最小值大于最大值时，代表不限制',
    `di_pk` TINYINT not null default 0 comment '是否主键',
    `di_autoed` TINYINT not null default 0 comment '是否自增，数值型自增依赖于数据库自身自增机制，字符串自增以来通过代码生成',
    `di_pwded` TINYINT not null default 0 comment '是否密码字段',
    `di_regex` VARCHAR (255) not null default '' comment '字段校验规则',
    `di_regex_msg` VARCHAR (255) not null default '' comment '校验规则错误信息',
    -- 显示信息
    `di_unit` VARCHAR (50) not null default '' comment '显示单位',
    `di_show_width` INT not null default 100 comment '显示宽度，-1代表自动宽度，0代表不显示，非0代表指定px',
    -- sql查询信息
    `di_sort` TINYINT not null default 0 comment '排序，奇数asc，偶数desc，数字越小越优先排序',
    -- fuzzy为非0时候，表示进行查询，1为全匹配，2为模糊匹配右匹配，3为模糊匹配左匹配，4为模糊匹配全匹配
    `di_fuzzy` TINYINT not null default 0 comment '模糊查询',
    `di_key_dict` INT not null default 0 comment '外键字典号',
    `di_key_table` VARCHAR (50) not null default '' comment '外键表名',
    `di_key_field` VARCHAR (50) not null default '' comment '外键表字段名',
    `di_key_show` VARCHAR (50) not null default '' comment '外键显示字段',
    `di_key_join_name` VARCHAR (50) not null default '' comment '外键表别名',
    `di_key_join_type` VARCHAR (10) not null default 'left' comment '外键表方式，inner,left,right',
    `di_key_condition` VARCHAR (50) not null default '' comment '外键表达式',
    `di_key_visible` TINYINT not null default 0 comment '外键是否显示',
    `di_key_width` INT not null default 0 comment '外键弹出界面宽度',
    `di_key_height` INT not null default 0 comment '外键弹出界面高度',
    `di_link_dict` INT not null default 0 comment '连接表字典号',
    `di_link_table` VARCHAR (50) not null default '' comment '链接表，优先与key_join_name一致，其次是key_table',
    `di_link_field` VARCHAR (50) not null default '' comment '链接字段，必须要有关联外键才可使用，主要为了从外键表取冗余数据，填写到界面对应字段中',
    `di_show_dict` INT not null default 0 comment '外显表字典号',
    `di_show_table` VARCHAR (50) not null default '' comment '外显表，优先与key_join_name一致，其次是key_table',
    `di_show_field` VARCHAR (50) not null default '' comment '外显字段，必须要有关联外键才可使用，主要是为了显示更多的字段，设置了外显则代表当前字段是虚拟字段不存在',
    -- 输入界面信息
    `di_default` VARCHAR (255) not null default '' comment '默认值',
    `di_required` TINYINT not null default 0 comment '是否必填项',
    `di_readonly` TINYINT not null default 0 comment '是否只读2新增；4修改',
    `di_inputed` TINYINT not null default 1 comment '增删改查页面是否显示字段，1刷新；2新增；4修改；8读取；16删除，可组合',
    `di_input_width` INT not null default 0 comment '字段输入框长度，0为不限制',
    `di_show_order` INT not null default 1000 comment '字段显示顺序，从小到大',
    `di_curd` INT not null default 15 comment '增改查配置项，1刷新；2新增；4修改；8读取；16删除，可组合',
    `di_group` VARCHAR (50) not null default '' comment '分组',
    `di_select` VARCHAR (255) not null default '' comment '下拉选择或其他附加信息，用;分割',
    `di_filtered` TINYINT not null default 0 comment '是否是筛选条件，用于前端是否显示搜索框，1：是；0：否',
    `di_app_type` TINYINT not null default 0 comment '字典适用的应用类型，0-通用;1=管理员；2=服务商；3=医院',
    `di_remark` VARCHAR (255) not null default '' comment '备注',
    primary key (`di_id`),
    key `idx_di_dict` (`di_dict`) using BTREE,
    key `idx_di_fieldname` (`di_fieldname`) using BTREE
) comment = '字典项表';
-- ----------------------------
-- Table structure for `{$database_prefix}_config`
-- 配置表
-- ----------------------------
drop table if exists `{$database_prefix}_config`;
create table `{$database_prefix}_config` (
    `conf_id` INT not null auto_increment,
    `conf_name` VARCHAR (100) not null comment '配置名称',
    `conf_value` text not null comment '配置值',
    `conf_content` text comment '内容说明',
    primary key (`conf_id`),
    unique key `uniq_conf_name` (`conf_name`) using BTREE
) comment = '配置表';
-- ----------------------------
-- Table structure for {$database_prefix}_menu
-- 菜单表  500
-- ----------------------------
drop table if exists `{$database_prefix}_menu`;
create table `{$database_prefix}_menu` (
    `mn_id` INT not null auto_increment comment '主键',
    `mn_app_type` TINYINT not null default 3 comment '应用类型，1=管理员；2=服务商；3=医院',
    `mn_code` VARCHAR (20) not null comment '菜单编码，两位一级，以MN开头，例如MN01',
    `mn_parent_code` VARCHAR (20) not null default '' comment '菜单父编码',
    `mn_title` VARCHAR (50) not null comment '菜单名称',
    `mn_path` VARCHAR (255) not null default '' comment '菜单完整路径，子级菜单用-分隔，例如：MN01-MN0101',
    `mn_sort` INT not null default '1000' comment '排序，由大到小',
    `mn_level` TINYINT not null default 0 comment '菜单级别',
    `mn_parented` TINYINT not null default 0 comment '是否为父级菜单 0=否 1=是',
    `mn_state` TINYINT not null default '1' comment '状态 0=关闭 1=开启',
    `mn_css` VARCHAR (255) not null default '' comment '菜单样式',
    `mn_style` TINYINT not null default 1 comment '菜单类型 0-不显示；1-侧边栏菜单；2-tabBar菜单',
    `mn_icon` VARCHAR (255) not null default '' comment '菜单图标',
    `mn_uri` VARCHAR (255) not null default '' comment '菜单地址',
    primary key (`mn_id`),
    key `idx_mn_code` (`mn_code`) using BTREE,
    key `idx_mn_parent_code` (`mn_parent_code`) using BTREE,
    key `idx_mn_path` (`mn_path`) using BTREE,
    key `un_idx_menu_info` (`mn_app_type`, `mn_state`, `mn_code`) using BTREE,
    key `idx_mn_state` (`mn_state`) using BTREE
) comment '菜单表';
-- ----------------------------
-- Table structure for {$database_prefix}_function
-- 功能  501
-- ----------------------------
drop table if exists `{$database_prefix}_function`;
create table `{$database_prefix}_function` (
    `fn_id` INT not null auto_increment comment '主键',
    `fn_code` VARCHAR (20) not null comment '功能编码，以FN开头，两位一组，FN00固定表示是否显示当前菜单',
    `fn_menu_code` VARCHAR (20) not null comment '功能所属菜单编码',
    `fn_name` VARCHAR (50) not null comment '功能名称',
    `fn_app_type` TINYINT not null default 3 comment '应用类型，1=管理员；2=服务商；3=医院',
    `fn_css` VARCHAR (255) not null default '' comment '样式',
    `fn_style` TINYINT not null default 1 comment '类型 0=不显示 1=上方按钮 2=行内按钮 4=列表按钮',
    `fn_state` TINYINT not null default 1 comment '状态 0=关闭 1=开启',
    `fn_type` VARCHAR (100) not null default 'default' comment '描述按钮类型，前端会展示不同样式',
    primary key (`fn_id`),
    key `un_idx_function_menu_info` (`fn_app_type`, `fn_menu_code`) using BTREE,
    key `idx_function_info` (`fn_app_type`, `fn_state`, `fn_code`) using BTREE
) comment '功能表';
-- ----------------------------
-- Table structure for {$database_prefix}_function_detail
-- 功能明细  502
-- ----------------------------
drop table if exists `{$database_prefix}_function_detail`;
create table `{$database_prefix}_function_detail` (
    `fd_id` INT not null auto_increment,
    `fd_function_code` VARCHAR (20) not null default '' comment '功能编码，关联function表fn_code字段',
    `fd_module` VARCHAR (50) not null default '' comment '权限模块',
    `fd_controller` VARCHAR (50) not null default '' comment '权限控制器',
    `fd_action` VARCHAR (50) not null default '' comment '权限动作',
    `fd_app_type` TINYINT not null default 3 comment '应用类型，1=管理员；2=服务商；3=医院',
    primary key (`fd_id`),
    key `un_idx_fd_function_code` (`fd_app_type`, `fd_function_code`) using BTREE,
    key `un_idx_fd_uri` (
        `fd_app_type`,
        `fd_module`,
        `fd_controller`,
        `fd_action`
    ) using BTREE
) comment '功能明细表';
-- ----------------------------
-- Table structure for `{$database_prefix}_user`
-- 用户表  503
-- ----------------------------
drop table if exists `{$database_prefix}_user`;
create table `{$database_prefix}_user` (
    `usr_id` INT not null auto_increment comment '主键',
    `usr_app_type` TINYINT not null default 3 comment '应用类型，1=管理员；2=服务商；3=医院',
    `usr_hospital` INT not null default 0 comment '机构信息  关联{$database_prefix}_hospital',
    `usr_mp` VARCHAR (255) not null default '' comment '手机号码',
    `usr_account` VARCHAR (50) not null default '' comment '用户账号',
    `usr_pwd` VARCHAR (80) not null default '68b6b4ab792a4476db8f6937bb4c4d12' comment '密码123456',
    `usr_salt` VARCHAR (4) not null default 'RzyL' comment '用户盐值',
    `usr_real_name` VARCHAR (50) not null default '' comment '真实姓名',
    `usr_sex` VARCHAR (4) not null default '' comment '性别',
    `usr_cert_type` VARCHAR (10) not null default '11' comment '证件类型',
    `usr_cert_code` VARCHAR (20) not null default '' comment '证件编码',
    `usr_cert_front_file` INT not null default 0 comment '证件正面 关联file 插入fileid',
    `usr_cert_reverse_file` INT not null default 0 comment '证件反面  关联file 插入fileid',
    `usr_source` VARCHAR (255) not null default '' comment '渠道来源（his用户或线下等系统来源标记）',
    `usr_source_pk` VARCHAR (255) not null default '' comment '渠道方标识',
    `usr_remark` VARCHAR (255) not null default '' comment '用户备注',
    `usr_login_time` BIGINT not null default 0 comment '登录时间',
    `usr_login_num` INT not null default 0 comment '登录次数',
    `usr_login_ip` VARCHAR (50) not null default '' comment '登录ip',
    `usr_img_head_file` INT not null default 0 comment '关联{$database_prefix}_file的最新头像的存储ID',
    `usr_img_head_url` VARCHAR (500) not null default '' comment '头像',
    `usr_mail` VARCHAR (500) not null default '' comment '邮箱',
    `usr_wwx_account` VARCHAR (32) not null default '' comment '企微的账号',
    `usr_tcc_account` VARCHAR (255) not null default '' comment '呼叫中心的账号',
    `usr_need_change` INT not null default 0 comment '是否需要强制修改  非零需要：0-无需;1-his新建;2-企业微信新建',
    `usr_pwd_update_time` BIGINT not null default 0 comment '最后一次短信修改时间',
    `usr_state` TINYINT not null default 1 comment '状态 0=关闭 1=开启',
    `usr_create_user` INT not null default 0 comment '创建人',
    `usr_create_time` BIGINT not null default 0 comment '创建时间',
    `usr_update_time` BIGINT not null default 0 comment '修改时间',
    `usr_delete_time` BIGINT not null default 0 comment '删除时间',
    primary key (`usr_id`),
    key `idx_usr_create_time` (`usr_create_time`) using BTREE,
    key `idx_usr_delete_time` (`usr_delete_time`) using BTREE,
    key `idx_usr_mp` (`usr_mp`) using BTREE,
    unique key `un_uniq_usr_info` (
        `usr_app_type`,
        `usr_account`,
        `usr_delete_time`
    ) using BTREE,
    key `idx_usr_cert_code` (`usr_cert_code`) using BTREE
) comment = '用户表';
-- ----------------------------
-- Table structure for {$database_prefix}_user_session
-- 用户会话记录
-- ----------------------------
drop table if exists `{$database_prefix}_user_session`;
create table `{$database_prefix}_user_session` (
    `us_id` INT not null auto_increment,
    `us_app_type` TINYINT not null default 3 comment '应用类型，1=管理员；2=服务商；3=医院',
    `us_user` INT not null default 0 comment '用户，关联user表主键',
    `us_session` VARCHAR (32) not null default 0 comment '会话id',
    `us_ip` VARCHAR (50) not null default '' comment '登录ip',
    `us_expire_in` INT not null default 7200 comment '会话有效期',
    `us_create_time` BIGINT not null default 0 comment '创建时间',
    `us_delete_time` BIGINT not null default 0 comment '删除时间',
    primary key (`us_id`),
    key `idx_us_app_type` (`us_app_type`) using BTREE,
    key `idx_us_user` (`us_user`) using BTREE,
    key `idx_us_delete_time` (`us_delete_time`) using BTREE,
    key `idx_us_create_time` (`us_create_time`) using BTREE
) comment '用户会话记';
-- ----------------------------
-- Table structure for {$database_prefix}_user_log
-- 用户日志
-- ----------------------------
drop table if exists `{$database_prefix}_user_log`;
create table `{$database_prefix}_user_log` (
    `ul_id` INT not null auto_increment,
    `ul_app_type` TINYINT not null default 3 comment '应用类型，1=管理员；2=服务商；3=医院',
    `ul_user` INT not null default 0 comment '用户，关联user表主键',
    `ul_ip` VARCHAR (50) not null default '' comment '登录ip',
    `ul_module` VARCHAR (100) not null default '' comment '操作模块',
    `ul_controller` VARCHAR (100) not null default '' comment '操作控制器',
    `ul_action` VARCHAR (255) not null default '' comment '操作函数',
    `ul_remark` VARCHAR (255) not null default '' comment '备注',
    `ul_extend` JSON default null comment '扩展信息存储的是本次修改的所有数据',
    `ul_response_elapsed_time` BIGINT not null default 0 comment '响应时间',
    `ul_create_time` BIGINT not null default 0 comment '创建时间',
    `ul_update_time` BIGINT not null default 0 comment '修改时间',
    `ul_delete_time` BIGINT not null default 0 comment '删除时间',
    primary key (`ul_id`),
    key `idx_ul_create_time` (`ul_create_time`) using BTREE,
    key `idx_ul_delete_time` (`ul_delete_time`) using BTREE
) comment '用户日志';
-- ----------------------------
-- Table structure for {$database_prefix}_role
-- 角色
-- ----------------------------
drop table if exists `{$database_prefix}_role`;
-- ----------------------------
-- 角色层级 是否是系统角色和医院相互关联使用
-- 是否为系统层级。标识系统新建的角色，只能在admin端新建修改
-- level标识新建层级高低。本来作用标识系统层级，与角色本身无关，但是在使用过程发现存在问题：对于底层及角色用户赋予角色新建编辑的角色，会出现无法限制可以操作的角色
-- 对于一般用户，查看角色为：指定医院|服务商的所有角色+系统角色
-- ----------------------------
create table `{$database_prefix}_role` (
    `r_id` INT not null auto_increment comment '主键',
    `r_app_type` TINYINT not null default 0 comment '应用类型，1=管理员；2=服务商；3=医院',
    -- 该配置用于配置是哪个用户创建的，begin
    `r_join_table` VARCHAR (50) not null default '' comment '关联表表名称',
    `r_join_field` VARCHAR (50) not null default '' comment '关联表表字段',
    `r_join_data` INT not null default 0 comment '关联表表数据',
    -- 该配置用于配置是哪个用户创建的，end
    `r_level` TINYINT not null default 1 comment '角色层级，最高级管理员请手动插入0',
    `r_is_system` TINYINT not null default 0 comment '是否系统配置  0=否',
    `r_name` VARCHAR (50) not null comment '角色名称',
    `r_mark` VARCHAR (255) not null default '' comment '备注',
    `r_state` TINYINT not null default 1 comment '状态 0=关闭 1=开启',
    `r_create_user` INT not null default 0 comment '新建人,关联user用户表id',
    `r_create_time` BIGINT not null default 0 comment '创建时间',
    `r_update_time` BIGINT not null default 0 comment '修改时间',
    `r_delete_time` BIGINT not null default 0 comment '删除时间',
    primary key (`r_id`),
    key `idx_r_create_time` (`r_create_time`) using BTREE
) comment '角色表';
-- ----------------------------
-- Table structure for {$database_prefix}_role_privilege
-- 角色权限表
-- ----------------------------
drop table if exists `{$database_prefix}_role_privilege`;
create table `{$database_prefix}_role_privilege` (
    `rp_id` INT not null auto_increment comment '主键',
    `rp_role` INT not null comment '角色id',
    `rp_function_code` VARCHAR (20) not null comment '功能编码',
    `rp_app_type` TINYINT not null default 3 comment '应用类型，1=管理员；2=服务商；3=医院',
    primary key (`rp_id`),
    key `un_idx_role_privilege_info` (`rp_app_type`, `rp_role`, `rp_function_code`) using BTREE
) comment '角色权限关联表';
-- ----------------------------
-- Table structure for {$database_prefix}_user_privilege
-- 用户权限
-- ----------------------------
drop table if exists `{$database_prefix}_user_privilege`;
create table `{$database_prefix}_user_privilege` (
    `up_id` INT not null auto_increment,
    `up_user` INT not null default 0 comment '用户id，关联user表usr_id字段',
    `up_app_type` TINYINT not null default 3 comment '应用类型，1=管理员；2=服务商；3=医院',
    `up_function_code` VARCHAR (20) not null default '' comment '功能编码，关联function表fn_code字段',
    primary key (`up_id`),
    key `un_idx_user_privilege_info` (`up_app_type`, `up_user`, `up_function_code`) using BTREE
) comment '用户权限表';
-- ----------------------------
-- Table structure for {$database_prefix}_relation
-- 用户角色关联
-- ----------------------------
drop table if exists `{$database_prefix}_relation`;
create table `{$database_prefix}_relation` (
    `rel_id` INT not null auto_increment,
    `rel_user` INT not null default 0 comment '用户id，关联user表usr_id字段',
    `rel_role` INT not null default 0 comment '角色id，关联role表r_id字段',
    `rel_app_type` TINYINT not null default 3 comment '应用类型，1=管理员；2=服务商；3=医院',
    `rel_role_level` TINYINT not null default 1 comment '角色层级，最高级管理员手动插入0',
    primary key (`rel_id`),
    key `un_idx_relation_info` (`rel_app_type`, `rel_role`, `rel_user`) using BTREE,
    key `idx_user_info` (`rel_user`) using BTREE
) comment '用户角色关联表';
-- ----------------------------
-- Table structure for {$database_prefix}_file
-- 文件数据表  510
-- ----------------------------
drop table if exists `{$database_prefix}_file`;
create table `{$database_prefix}_file` (
    `f_id` INT not null auto_increment comment 'ID',
    `f_name` VARCHAR (100) not null default '' comment '文件名称',
    `f_path` VARCHAR (500) not null default '' comment '文件存储路径',
    `f_url` VARCHAR (500) not null default '' comment '对外地址',
    `f_type` VARCHAR (50) not null default '' comment '文件类型',
    `f_app_type` TINYINT not null default 3 comment '应用类型，1=管理员；2=服务商；3=医院',
    `f_state` TINYINT not null default 0 comment '使用状态 0未使用 1正常',
    `f_group` VARCHAR (50) not null default '' comment '分组  public private',
    `f_dataid` INT not null default 0 comment '主表数据',
    `f_table` VARCHAR (50) not null default '' comment '主表名称',
    `f_field` VARCHAR (50) not null default '' comment '主表字段',
    `f_driver` VARCHAR (50) not null default '' comment '上传文件的驱动',
    `f_access` INT not null default 0 comment '1验证权限 0不验证权限',
    `f_create_user` INT not null default 0 comment '创建人',
    `f_create_time` BIGINT not null default 0 comment '创建时间',
    `f_update_time` BIGINT not null default 0 comment '修改时间',
    `f_delete_time` BIGINT not null default 0 comment '删除时间',
    primary key (`f_id`) using BTREE,
    key `idx_f_app_type` (`f_app_type`) using BTREE,
    key `idx_f_state` (`f_state`) using BTREE,
    key `idx_f_create_time` (`f_create_time`) using BTREE,
    key `idx_f_delete_time` (`f_delete_time`) using BTREE,
    key `idx_f_group` (`f_group`) using BTREE,
    key `idx_f_table` (`f_table`) using BTREE,
    key `idx_f_field` (`f_field`) using BTREE
) comment = '文件数据表';
drop table if exists `{$database_prefix}_task_record`;
create table `{$database_prefix}_task_record` (
    `tr_id` int unsigned not null auto_increment comment '主键',
    `tr_date` int not null default 0 comment '执行日期',
    `tr_name` varchar(100) not null default '' comment '进程类名',
    `tr_begin_time` int not null default 0 comment '开始执行时间',
    `tr_end_time` int not null default 0 comment '结束执行时间',
    `tr_execute_num` int not null default 1 comment '执行次数',
    `tr_extend` json default null comment '执行后扩展信息',
    `tr_state` tinyint not null default 1 comment '执行状态，1执行中，2执行完成',
    `tr_create_time` int not null default 0 comment '创建时间',
    `tr_update_time` int not null default 0 comment '修改时间',
    `tr_delete_time` int not null default 0 comment '删除时间',
    primary key (`tr_id`),
    key `idx_tr_date` (`tr_date`) using BTREE,
    key `idx_tr_name` (`tr_name`) using BTREE,
    key `idx_tr_state` (`tr_state`) using BTREE
) comment '任务执行记录';

-- ----------------------------
-- Table structure for {$database_prefix}_mq_common_task
-- mq通用任务表
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_mq_common_task`;
CREATE TABLE `{$database_prefix}_mq_common_task` (
    `mct_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
    `mct_app_type` int not null default 0 comment '应用类型，1系统、2医院、3服务商',
    `mct_type` int not null default 0 comment '任务类型，通过该字段区分不同任务',
    `mct_pk` int not null DEFAULT 0 comment '关联应用表主键id',
    `mct_name` varchar(50) not null default '' comment '任务名称',
    `mct_admin` int NOT NULL default 0 COMMENT '任务发布用户id，根据类型不同关联不同表',
    `mct_action` varchar(250) not null default '' comment '任务完整函数类名',
    `mct_params` text comment '任务参数',
    `mct_state` tinyint not null default 1 comment '任务状态，1等待处理、2处理中、3处理成功、4处理失败',
    `mct_result` text comment '任务处理结果',
    `mct_src_table` VARCHAR(100) not null default '' comment '来源表名',
    `mct_src_field` varchar(50) not null default '' comment '来源表主键字段',
    `mct_src_id` int not null default 0 comment '来源表主键id',
    `mct_create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
    `mct_process_time` int NOT NULL DEFAULT 0 COMMENT '处理时间',
    `mct_finish_time` int NOT NULL DEFAULT 0 COMMENT '完成时间',
    `mct_update_time` int NOT NULL DEFAULT 0 COMMENT '修改时间',
    `mct_delete_time` int NOT NULL DEFAULT 0 COMMENT '删除时间',
    PRIMARY KEY (`mct_id`),
    KEY `un_idx_mct_admin` (`mct_pk`, `mct_admin`) USING BTREE,
    KEY `idx_mct_state` (`mct_state`) USING BTREE,
    KEY `idx_mct_create_time` (`mct_create_time`) USING BTREE,
    KEY `idx_mct_delete_time`(`mct_delete_time`) USING BTREE,
    KEY `idx_mct_source` (`mct_src_table`, `mct_src_field`, `mct_src_id`) USING BTREE
) COMMENT 'mq通用任务表';

-- ----------------------------
-- Table structure for {$database_prefix}_task_type
-- 任务类型码表
-- ----------------------------
DROP TABLE IF EXISTS `{$database_prefix}_task_type`;
CREATE TABLE `{$database_prefix}_task_type` (
  `tt_code` char(10) NOT NULL DEFAULT '' COMMENT '任务类型编码',
  `tt_name` varchar(100) NOT NULL DEFAULT '' COMMENT '任务类型别名',
  KEY `idx_tt_code` (`tt_code`)
) COMMENT='任务类型码表';

