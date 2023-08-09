<?php

declare(strict_types=1);

return [
    'success' => '成功',
    'error' => '错误',
    'fail' => '失败',
    // 错误码相关
    'access_token_valid_fail' => '身份令牌验证失败', // 10
    'access_token_existed' => '已存在有效的身份令牌', // 11
    'access_refresh_token_valid_fail' => '刷新令牌验证失败', // 12
    'request_validate_error' => '请求参数验证失败<{:content}>', // 13
    'access_token_existed' => '已存在有效的身份令牌', // 14
    'access_token_refresh_not_existed' => '刷新令牌已失效', // 15
    'access_token_create_error' => '生成身份令牌失败', // 16
    'access_token_refresh_error' => '刷新身份令牌失败', // 17
    'access_token_data_get_error' => '获取身份令牌属性错误', // 18
    'access_token_type_error' => '令牌类型不正确', // 19
    'no_request_data' => '未获取到有效的请求数据', // 20
    'data_save_error' => '保存数据失败', // 21
    'data_update_error' => '更新数据失败', // 22
    'data_delete_error' => '删除数据失败', // 23
    'data_query_error' => '查询数据错误', // 24
    'data_not_found' => '未查询到有效的数据<{:name}>', // 25
    'data_validate_error' => '数据验证失败<{:content}>', // 26
    'model_not_exist' => '数据模型不存在', // 27
    'data_exist_error' => '名称或编号已存在', // 28
    'data_privilege_error' => '无权访问该数据', // 29
    'data_gt_max' => '{:name}数据不能大于<{:max}>', // 30
    'data_lt_min' => '{:name}数据不能小于<{:min}>', // 31
    'data_regex_fail' => '{:name}数据校验失败<{:content}>', // 32
    'data_required' => '{:name}数据不能为空', // 33
    'data_max_error' => '上传数据大于存入最大值', // 34
    'data_relation_exist' => '当前数据存在逻辑关联数据', // 35
    'data_child_relation_exist' => '当前数据仍有关联子数据', // 36
    'data_state_error' => '当前数据状态异常<{:content}>', // 37
    'data_can_not_used' => '当前数据状态不符合操作要求', // 38
    'dict_data_not_exist' => '字典数据不存在<{:id}>', // 40
    'dict_item_not_exist' => '字典项数据不存在<{:id}>', // 41
    'dict_id_not_exist' => '字典编号不存在<{:id}-{:name}>', // 42
    'dict_range_error' => '字典编号范围错误', // 43
    'session_not_exist' => '会话不存在', // 80
    'access_not_allowed' => '无权访问', // 81
    'login_info_not_correct' => '登录信息不正确', // 82
];
