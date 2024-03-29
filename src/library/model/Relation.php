<?php

declare(strict_types=1);

namespace alocms\model;

use alocms\constant\Common as CommonConst;
use think\db\Query;
use think\model\relation\BelongsTo;

/**
 * 用户角色关系模型
 */
class Relation extends Base
{
    /** @inheritDoc */
    protected $table = '{$database_prefix}_relation';
    /** @inheritDoc */
    protected $pk = 'rel_id';
    /** @inheritDoc */
    protected $prefix = 'rel_';

    /**
     * 获取角色
     * 
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'rel_role', 'r_id');
    }

    /**
     * 获取指定角色的所有用户
     *
     * @param integer|array $role 角色id
     * @param integer $appType 应用类型
     * @return Query
     */
    public function getUserByRole($role, int $appType = CommonConst::APP_TYPE_ORGANIZATION): Query
    {
        $query = $this->baseAppTypeQuery($appType);
        if (\is_array($role)) {
            $query = $query->whereIn('rel_role', $role);
        } else {
            $query = $query->where('rel_role', $role);
        }
        return $query;
    }

    /**
     * 获取指定用户的所有角色
     *
     * @param integer $user 用户id
     * @param integer $appType 应用类型
     * @return Query
     */
    public function getRoleByUser(int $user, int $appType = CommonConst::APP_TYPE_ORGANIZATION): Query
    {
        return $this->baseAppTypeQuery($appType)
            ->alias('rel')
            ->join('Role r', 'r.r_id =rel.rel_role', 'inner')
            ->where('r_state', 1) //角色是开启的才可以使用
            ->where('rel_user', $user);
    }
    /**
     * 获取指定用户的最大和最小角色id
     *
     * @param integer $user 用户id
     * @param integer $appType 应用类型
     * @return Query
     */
    public function getUserRoleLevel(int $user, int $appType = CommonConst::APP_TYPE_CORPORATION): Query
    {
        return $this->baseAppTypeQuery($appType)
            ->where('r_state', 1)
            ->where('rel_user', $user)
            ->fieldRaw('max(rel_role_level) as max_level , min(rel_role_level) as min_level');
    }
}
