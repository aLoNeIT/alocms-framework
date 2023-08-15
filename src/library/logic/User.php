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
class User extends Base
{
    /**
     * 获取用户角色级别
     *
     * @param integer $user 用户id
     * @param integer $appType 应用类型
     * @return JsonTable 返回JsonTable对象，data节点是一个多维数组，每个元素包含id和name两个节点
     */
    public function getRole(int $user, int $appType = CommonConst::APP_TYPE_ORGANIZATION): JsonTable
    {
        try {
            // 查询用户角色信息
            $relation = RelationModel::instance()->getRoleByUser($user, $appType)
                ->field('rel.*,r.*')
                ->order('rel_role_level desc')
                ->select();
            if ($relation->isEmpty()) {
                return ErrCodeFacade::getJError(500);
            }
            $result = [];
            foreach ($relation as $item) {
                $result[] = [
                    'id' => $item->rel_role,
                    'name' => $item->r_name
                ];
            }
            return $this->jsonTable->successByData($result);
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }
}
