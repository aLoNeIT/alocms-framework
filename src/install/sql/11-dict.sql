-- 基础字典表
--   d_tablename使用驼峰命名法
--   100以内不可用 ；
--   100-300为码表;
--   500-1000为框架所需要的表；
--   1000以上为不同业务系统需要的表，若同一个表针对不同应用设计，需要建立不同的字典，而且字典编号按照规范走

-- 证件类型100
delete from `{$database_prefix}_dict` where d_id=100;
insert into `{$database_prefix}_dict`(`d_id`,`d_name`,`d_tablename`,`d_sub`,`d_prefix`)
values(100,'证件类型','CertificateType','','ct_');
delete from `{$database_prefix}_dict_item` where di_dict=100;
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_pk`,`di_autoed`)
values(100,'编码','ct_code',6,10,0,1,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(100,'名称','ct_name',6,50,0);

-- 国家101
delete from `{$database_prefix}_dict` where d_id=101;
insert into `{$database_prefix}_dict`(`d_id`,`d_name`,`d_tablename`,`d_sub`,`d_prefix`)
values(101,'国家','Country','','c_');

delete from `{$database_prefix}_dict_item` where di_dict=101;
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_pk`,`di_autoed`)
values(101,'编码','c_code',6,10,0,1,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(101,'名称','c_title',6,50,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(101,'拼音','c_py',6,50,0);

-- 民族102
delete from `{$database_prefix}_dict` where d_id=102;
insert into `{$database_prefix}_dict`(`d_id`,`d_name`,`d_tablename`,`d_sub`,`d_prefix`)
values(102,'民族','Nation','','na_');

delete from `{$database_prefix}_dict_item` where di_dict=102;
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_pk`,`di_autoed`)
values(102,'编码','na_code',6,10,0,1,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(102,'名称','na_name',6,50,0);

-- 行政区化103
delete from `{$database_prefix}_dict` where d_id=103;
insert into `{$database_prefix}_dict`(`d_id`,`d_name`,`d_tablename`,`d_sub`,`d_prefix`)
values(103,'行政区化表','HouseArea','','ha_');
delete from `{$database_prefix}_dict_item` where di_dict=103;
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_pk`,`di_autoed`,`di_filtered`,`di_fuzzy`)
values(103,'编码','ha_code',6,10,0,1,0,1,1);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_filtered`,`di_fuzzy`)
values(103,'名称','ha_name',6,50,0,1,4);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(103,'简称','ha_shortname',6,50,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(103,'拼音','ha_pinyin',6,50,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(103,'简拼','ha_pinyin_short',6,50,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(103,'城市编码','ha_citycode',10,50,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(103,'邮政编码','ha_zipcode',6,6,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(103,'上级地区','ha_parent',6,10,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_show_width`)
values(103,'是否拥有下级','ha_child',1,2,0,150);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(103,'级别','ha_level',1,2,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(103,'路径','ha_path',6,50,0);

-- 性别 104
delete from `{$database_prefix}_dict` where d_id=104;
insert into `{$database_prefix}_dict`(`d_id`,`d_name`,`d_tablename`,`d_sub`,`d_prefix`)
values(104,'性别','Sex','','sex_');

delete from `{$database_prefix}_dict_item` where di_dict=104;
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_pk`,`di_autoed`)
values(104,'性别编码','sex_code',6,20,0,1,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(104,'性别名称','sex_name',6,50,0);

-- 人员关系 105
delete from `{$database_prefix}_dict` where d_id=105;
insert into `{$database_prefix}_dict`(`d_id`,`d_name`,`d_tablename`,`d_sub`,`d_prefix`)
values(105,'人员关系','Relationship','','rs_');

delete from `{$database_prefix}_dict_item` where di_dict=105;
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_pk`,`di_autoed`)
values(105,'编码','rs_code',6,20,0,1,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(105,'名称','rs_name',6,50,0);

-- 人员状态 106
delete from `{$database_prefix}_dict` where d_id=106;
insert into `{$database_prefix}_dict`(`d_id`,`d_name`,`d_tablename`,`d_sub`,`d_prefix`)
values(106,'人员状态','PeopleStatus','','ps_');

delete from `{$database_prefix}_dict_item` where di_dict=106;
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_pk`,`di_autoed`)
values(106,'编码','ps_code',6,10,0,1,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(106,'名称','ps_name',6,50,0);

-- 文化程度107
delete from `{$database_prefix}_dict` where d_id=107;
insert into `{$database_prefix}_dict`(`d_id`,`d_name`,`d_tablename`,`d_sub`,`d_prefix`)
values(107,'文化程度','CultureStatus','','cs_');

delete from `{$database_prefix}_dict_item` where di_dict=107;
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_pk`,`di_autoed`)
values(107,'编码','cs_code',6,20,0,1,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(107,'名称','cs_name',6,50,0);

-- 媒介渠道108
delete from `{$database_prefix}_dict` where d_id=108;
insert into `{$database_prefix}_dict`(`d_id`,`d_name`,`d_tablename`,`d_sub`,`d_prefix`)
values(108,'媒介渠道','MediaChannel','','mc_');

delete from `{$database_prefix}_dict_item` where di_dict=108;
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_pk`,`di_autoed`)
values(108,'编码','mc_code',6,10,0,1,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(108,'名称','mc_name',6,50,0);

-- 职业109
delete from `{$database_prefix}_dict` where d_id=109;
insert into `{$database_prefix}_dict`(`d_id`,`d_name`,`d_tablename`,`d_sub`,`d_prefix`)
values(109,'职业','Career','','ca_');

delete from `{$database_prefix}_dict_item` where di_dict=109;
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_pk`,`di_autoed`)
values(109,'编码','ca_code',6,20,0,1,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(109,'名称','ca_name',6,50,0);

-- 婚姻状况110
delete from `{$database_prefix}_dict` where d_id=110;
insert into `{$database_prefix}_dict`(`d_id`,`d_name`,`d_tablename`,`d_sub`,`d_prefix`)
values(110,'婚姻状况','MaritalStatus','','ms_');

delete from `{$database_prefix}_dict_item` where di_dict=110;
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_pk`,`di_autoed`)
values(110,'编码','ms_code',6,20,0,1,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(110,'名称','ms_name',6,50,0);

-- 星级表 111
delete from `{$database_prefix}_dict` where d_id=111;
insert into `{$database_prefix}_dict`(`d_id`,`d_name`,`d_tablename`,`d_sub`,`d_prefix`)
values(111,'星级表','Stars','','st_');

delete from `{$database_prefix}_dict_item` where di_dict=111;
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_pk`,`di_autoed`)
values(111,'编码','st_code',6,10,0,1,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(111,'名称','st_name',6,50,0);

-- 等级表 112
delete from `{$database_prefix}_dict` where d_id=112;
insert into `{$database_prefix}_dict`(`d_id`,`d_name`,`d_tablename`,`d_sub`,`d_prefix`)
values(112,'等级表','Grade','','st_');

delete from `{$database_prefix}_dict_item` where di_dict=112;
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_pk`,`di_autoed`)
values(112,'编码','g_code',6,10,0,1,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(112,'名称','g_name',6,50,0);

-- 菜单500
delete from `{$database_prefix}_dict` where d_id=500;
insert into `{$database_prefix}_dict`(`d_id`,`d_name`,`d_tablename`,`d_sub`,`d_prefix`)
values(500,'菜单表','Menu','','mn_');

delete from `{$database_prefix}_dict_item` where di_dict=500;
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_pk`,`di_autoed`)
values(500,'序号','mn_id',1,-1,1,1,1);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_select`,`di_default`)
values(500,'应用类型','mn_app_type',1,3,0,'1-管理员;2-服务商;3-医院',3);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_required`)
values(500,'编码','mn_code',6,20,2,6);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_inputed`)
values(500,'父编码','mn_parent_code',6,20,2,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(500,'名称','mn_title',6,50,2);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_required`,`di_inputed`,`di_show_width` )
values(500,'完整路径','mn_path',6,255,2,6,0,150);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_default`)
values(500,'排序','mn_sort',1,999999,2,1000);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_required`,`di_inputed`)
values(500,'级别','mn_level',1,4,2,6,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_select`,`di_default`,`di_required`,`di_inputed`)
values(500,'是否父级','mn_parented',1,2,0,'0-否;1-是',0,6,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_select`,`di_default`)
values(500,'状态','mn_state',1,2,0,'0-关闭;1-开启',1);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(500,'样式','mn_css',6,255,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,`di_select`,`di_default`)
values(500,'类型','mn_style',1,2,0,'0-不显示;1-侧边栏菜单;2-tabBar菜单',1);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(500,'图标','mn_icon',6,255,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`)
values(500,'地址','mn_uri',6,255,0);
insert into `{$database_prefix}_dict_item`(`di_dict`,`di_name`,`di_fieldname`,`di_type`,`di_max`,`di_min`,
`di_key_dict`,`di_key_table`,`di_key_field`,`di_key_show`)
values(500,'页面','mn_page',1,-1,0,
1000,'Page','p_id','p_name');