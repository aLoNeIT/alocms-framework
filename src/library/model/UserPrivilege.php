<?php

declare(strict_types=1);

namespace alocms\model;

use think\db\Query;

/**
 * 用户权限模型
 */
class UserPrivilege extends Base
{
    /** @inheritDoc */
    protected $table = '{$database_prefix}_user_privilege';
    /** @inheritDoc */
    protected $pk = 'up_id';
    /** @inheritDoc */
    protected $prefix = 'up_';

    /**
     * 获取功能列表
     *
     * @param int $appType 应用类型
     * @param int $user 用户id
     * @return Query
     */
    public function getFunction(int $appType, int $user): Query
    {
        return $this->baseAppTypeQuery($appType)
            ->where('up_user', $user);
    }

    /**
     * 获取有指定功能的用户
     *
     * @param int $appType 应用类型
     * @param string $funCode 功能编码
     * @return Query
     */
    public function getUser(int $appType, string $funCode,): Query
    {
        $query = $this->baseAppTypeQuery($appType);
        $query = $query->where('up_function_code', $funCode);
        return $query;
    }
}
