<?php

declare(strict_types=1);

namespace alocms\model;

use alocms\constant\Common as CommonConst;
use think\db\Query;

/**
 * 角色权限模型
 */
class RolePrivilege extends Base
{
    /** @inheritDoc */
    protected $table = '{$database_prefix}_role_privilege';
    /** @inheritDoc */
    protected $pk = 'rp_id';
    /** @inheritDoc */
    protected $prefix = 'rp_';


    /**
     * 获取指定角色的功能
     *
     * @param integer|array $role 角色id
     * @param integer $appType 应用类型
     * @return Query
     */
    public function getFunction($role, int $appType = CommonConst::APP_TYPE_CORPORATION): Query
    {
        $query = $this->baseAppTypeQuery($appType);
        return \is_array($role) ? $query->whereIn('rp_role', $role) : $query->where('rp_role', $role);
    }


    /**
     * 获取角色
     *
     * @param string $funCode 功能编码
     * @param integer $appType 应用类型
     * @return Query
     */
    public function getRole(string $funCode, int $appType = CommonConst::APP_TYPE_CORPORATION): Query
    {
        $query = $this->baseAppTypeQuery($appType);
        $query = $query->where('rp_function_code', $funCode);
        return $query;
    }
}
