<?php

declare(strict_types=1);

namespace alocms\model;

use alocms\constant\Common as CommonConst;
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
     * @param int $user 用户id
     * @param int $appType 应用类型
     * @return Query
     */
    public function getFunction(int $user, int $appType = CommonConst::APP_TYPE_ORGANIZATION): Query
    {
        return $this->baseAppTypeQuery($appType)
            ->where('up_user', $user);
    }

    /**
     * 获取有指定功能的用户
     *
     * @param string $funCode 功能编码
     * @param int $appType 应用类型
     * @return Query
     */
    public function getUser(string $funCode, int $appType = CommonConst::APP_TYPE_ORGANIZATION): Query
    {
        $query = $this->baseAppTypeQuery($appType);
        $query = $query->where('up_function_code', $funCode);
        return $query;
    }
}
