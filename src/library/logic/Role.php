<?php

declare(strict_types=1);

namespace alocms\logic;

use alocms\constant\Common as CommonConst;
use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\model\Relation as RelationModel;
use alocms\util\{Helper, JsonTable};

/**
 * 角色处理逻辑类
 * @author alone <alone@alonetech.com>
 */
class Role extends Base
{
    /**
     * 获取用户角色级别
     *
     * @param integer $user 用户id
     * @param integer $appType 应用类型
     * @return JsonTable 返回JsonTable对象，data节点是一个数组，包含max_level和min_level两个节点
     */
    public function getUserLevel(int $user, int $appType = CommonConst::APP_TYPE_ORGANIZATION): JsonTable
    {
        try {
            // 查询用户角色信息
            $relation = RelationModel::instance()->getUserRoleLevel($user, $appType)->find();
            if (\is_null($relation)) {
                return ErrCodeFacade::getJError(500);
            }
            return $this->jsonTable->successByData([
                'max_level' => $relation->max_level ?? null,
                'min_level' => $relation->min_level ?? null
            ]);
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }
}
