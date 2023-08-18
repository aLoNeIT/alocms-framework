<?php

namespace alocms\logic;

use alocms\constant\{Common as CommonConst};
use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\logic\CommonConst as CommonConstLogic;
use alocms\logic\Menu as MenuLogic;
use alocms\logic\Role as RoleLogic;
use alocms\logic\Session as SessionLogic;
use alocms\logic\User as UserLogic;
use alocms\model\FunctionDetail as FunctionDetailModel;
use alocms\model\FunctionModel;
use alocms\model\Menu as MenuModel;
use alocms\model\Relation as RelationModel;
use alocms\model\Role as RoleModel;
use alocms\model\RolePrivilege as RolePrivilegeModel;
use alocms\model\UserPrivilege as UserPrivilegeModel;
use alocms\util\CmsException;
use alocms\util\Helper;
use alocms\util\JsonTable;

/**
 * 权限处理逻辑类
 */
class Privilege extends Base
{
    /**
     * 用户权限校验
     *
     * @return JsonTable
     */
    public function check(string $module, string $controller, string $action, array $function = [], int $appType = CommonConst::APP_TYPE_ORGANIZATION): JsonTable
    {
        // 该方法使用需要注意Request是否能够获取正确信息
        // 在框架流程未走到路由相关信息初始化的时候，可能无法获取路由信息
        try {
            // 查询mvc对应的功能编码
            $functionCode = FunctionDetailModel::instance()
                ->getCode($module, $controller, $action, $appType)
                ->column('fd_function_code');
            if (\is_null($functionCode)) {
                // 查询不到功能编码则提示无权访问
                return ErrCodeFacade::getJError(81);
            }
            $function = array_intersect($functionCode, $function);
            // 返回是否拥有权限
            return !empty($fun) ? $this->jsonTable->success() : ErrCodeFacade::getJError(81);
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 通过用户信息获取功能列表
     *
     * @param integer $user 用户id
     * @param integer $appType 应用类型
     * @return JsonTable 返回JsonTable对象，data节点为功能编码数组
     */
    public function getByUser(int $user, int $appType = CommonConst::APP_TYPE_ORGANIZATION): JsonTable
    {
        try {
            // 先获取用户权限
            $upFunctions = UserPrivilegeModel::instance()->getFunction($user, $appType)->column('up_function_code');
            // 获取用户关联角色列表
            $roles = RelationModel::instance()->getRoleByUser($user, $appType)->column('rel_role');
            // 获取角色权限
            $rpFunctions = RolePrivilegeModel::instance()->getFunction($roles, $appType)->column('rp_function_code');
            // 合并用户权限及角色权限
            $functions = array_merge($upFunctions, $rpFunctions);
            return $this->jsonTable->successByData($functions);
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 获取用户有权限的树形菜单
     *
     * @param integer $user 用户id
     * @param string|null $parentMenu 父级菜单编码
     * @param integer $appType 应用类型
     * @return JsonTable data节点为菜单显示功能编码数组
     */
    public function getMenuByUser(int $user, string $parentCode = null, int $appType = CommonConst::APP_TYPE_ORGANIZATION): JsonTable
    {
        try {
            // 获取所有菜单信息
            $jResult = Helper::throwifJError(MenuLogic::instance()->getByParent($parentCode, $appType));
            $menus = $jResult->data;
            // 获取用户权限集合
            $jResult = Helper::throwifJError($this->getByUser($user, $appType));
            $functions = $jResult->data;
            // 用户拥有权限的菜单集合
            $upMenus = [];
            // 提取具有权限部分菜单
            foreach ($menus as $menu) {
                $fnCode = \str_replace('MN', 'FN', $menu['mn_code']) . '00';
                if (\in_array($fnCode, $functions)) {
                    $upMenus[] = $menu;
                }
            }
            // 调用树形菜单构建函数
            return MenuLogic::instance()->buildTree($upMenus);
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }
}
