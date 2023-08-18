<?php

namespace alocms\util;

use think\facade\Config;

/**
 *  错误码处理类
 * 
 * @author alone <alone@alonetech.com>
 */
class ErrCode
{

    /**
     * JsonTable对象
     *
     * @var JsonTable
     */
    protected $jsonTable = null;
    /**
     * 错误码
     * - 0-499 框架基础错误
     * - 500-999 业务框架错误
     * - 1000以上 模块固定场景错误码
     *
     * @var array
     */
    protected $errorCode = [
        // 权限相关错误码
        '10' => 'access_token_valid_fail',
        '11' => 'access_token_not_existed',
        '12' => 'access_refresh_token_valid_fail',
        '13' => 'request_validate_error',
        '14' => 'access_token_existed',
        '15' => 'access_token_refresh_not_existed',
        '16' => 'access_token_create_error',
        '17' => 'access_token_refresh_error',
        '18' => 'access_token_data_get_error',
        '19' => 'access_token_type_error',
        // crud相关错误码
        '20' => 'no_request_data',
        '21' => 'data_save_error',
        '22' => 'data_update_error',
        '23' => 'data_delete_error',
        '24' => 'data_query_error',
        '25' => 'data_not_found',
        '26' => 'data_validate_error',
        '27' => 'model_not_exist',
        '28' => 'data_exist_error',
        '29' => 'data_privilege_error',
        '30' => 'data_gt_max',
        '31' => 'data_lt_min',
        '32' => 'data_regex_fail',
        '33' => 'data_required',
        '34' => 'data_max_error',
        '35' => 'data_relation_exist',
        '36' => 'data_child_relation_exist',
        '37' => 'data_state_error',
        '38' => 'data_can_not_used',
        // 字典相关
        '40' => 'dict_data_not_exist',
        '41' => 'dict_item_not_exist',
        '42' => 'dict_id_not_exist',
        '43' => 'dict_range_error',
        // 会话相关
        '80' => 'session_not_exist',
        '81' => 'access_not_allowed',
        '82' => 'login_info_incorrect',
        '83' => 'login_failed_too_many_times',
        '84' => 'login_account_or_password_incorrect',
        // 字典相关
        '100' => 'dict_property_not_exists',
        '101' => 'dictitem_fields_empty',
        // 业务层用户角色相关
        '500' => 'user_role_not_exist',
        '550' => 'privilege_error',
        '551' => 'privilege_menu_not_found',
        '552' => 'privilege_function_not_find',
        '560' => 'privilege_role_not_find_menu',
        '561' => 'privilege_role_not_find_function',
        '565' => 'privilege_user_not_find_menu',
        '566' => 'privilege_user_not_find_function',

        // 业务层辅助功能
        '600' => 'captcha_incorrect',
        // 用户相关
        '650' => 'user_closed',
        // 机构相关
        '660' => 'organization_closed',
        // 集团相关
        '670' => 'corporation_closed',
    ];
    /**
     * 构造函数
     */
    public function __construct()
    {
        //重新实例化
        $this->jsonTable = app('JsonTable', [], true);
        //载入错误码配置文件
        $this->errorCode = \array_merge($this->errorCode, Config::get('errcode', []));
    }
    /**
     * 获取错误信息
     *
     * @param string|integer $state 错误码
     * @param array $param 错误信息参数，部分错误支持
     * @return array 返回错误信息数组
     */
    public function getError($state, $param = []): array
    {
        return $this->getJError($state, $param, false)->toArray();
    }

    /**
     * 获取JsonTable对象的错误
     *
     * @param string|integer $state 错误状态码
     * @param array $param 额外信息参数
     * @param boolean $clone 是否克隆
     * @return JsonTable 返回包含错误信息的JsonTable对象
     */
    public function getJError($state, array $param = [], bool $clone = true): JsonTable
    {
        return $this->getJErrorWithData($state, $param, null, $clone);
    }

    /**
     * 获取JsonTable对象的错误 包含data
     *
     * @param string|integer $state 错误状态码
     * @param array $param 额外信息参数
     * @param mixed $data 错误附带信息
     * @param boolean $clone 是否克隆
     * @return JsonTable 返回包含错误信息的JsonTable对象
     */
    public function getJErrorWithData($state, array $param = [], $data = null, bool $clone = true): JsonTable
    {
        $state = strval($state);
        $msg = isset($this->errorCode[$state]) ? lang($this->errorCode[$state], $param) : '';
        return $clone ? $this->jsonTable->withMessage($msg, $state, $data) : $this->jsonTable->message($msg, $state, $data);
    }

    /**
     * 获取错误文本
     *
     * @param string|integer $state 错误码
     * @param array $param 额外信息参数
     * @return string 返回错误文本
     */
    public function getErrText($state, array $param = []): string
    {
        $state = strval($state);
        return isset($this->errorCode[$state]) ? lang($this->errorCode[$state], $param) : '';
    }

    /**
     * 判断错误码是否存在
     *
     * @param string|integer $state 错误码
     * @return boolean 返回错误码是否存在的布尔结果
     */
    public function exists($state): bool
    {
        return isset($this->errorCode[strval($state)]);
    }
}
