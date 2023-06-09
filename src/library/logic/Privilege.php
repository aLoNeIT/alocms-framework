<?php

namespace alocms\logic;

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
    public function check(): JsonTable
    {
        // 该方法使用需要注意Request是否能够获取正确信息
        // 在框架流程未走到路由相关信息初始化的时候，可能无法获取路由信息
        // 获取请求mvc信息
        /** @var \alocms\Request $request */
        $request = $this->app->request;
        $module = $this->app->http->getName();
        $controller = $request->controller();
        $action = $request->action();
        // 从配置文件获取白名单信息
        $whiteList = $this->app->config->get('system.white_list.privilege');
        $path = \strtolower(\implode('/', [$module, $controller, $action]));
        if (\in_array($path, $whiteList)) {
            // 若匹配到白名单则直接跳过
            return $this->jsonTable->success();
        }
        // 查询mvc对应的功能编码
        $functionCode = FunctionDetailModel::instance()->getCode($request->appType(), $module, $controller, $action)
            ->column('fd_function_code');
        if (\is_null($functionCode)) {
            // 查询不到功能编码则提示无权访问
            return ErrCodeFacade::getJError(81);
        }
        // 获取当前用户session内的权限内容
        $jResult = SessionLogic::instance()->getFunction();
        if ($jResult->isSuccess()) {
            $userFunction = $jResult->msg;
        }
        $fun = array_intersect($functionCode, $userFunction);
        // 返回是否拥有权限
        return !empty($fun) ? $this->jsonTable->success() : ErrCodeFacade::getJError(81);
    }

    /**
     * 通过用户信息获取功能列表
     *
     * @param integer $appType 应用类型
     * @param integer $user 用户id
     * @return JsonTable data节点为功能编码数组
     */
    public function getByUser(int $appType, int $user): JsonTable
    {
        // 先获取用户权限
        $upData = UserPrivilegeModel::instance()->getFunction($appType, $user)->column('up_function_code');
        // 获取用户关联角色列表
        $role = RelationModel::instance()->getRoleByUser($appType, $user)->column('rel_role');
        // 获取角色权限
        $rpData = RolePrivilegeModel::instance()->getFunction($appType, $role)->column('rp_function_code');
        // 合并用户权限及角色权限 ---20201214修改合并为交集
        $function = array_merge($upData, $rpData); //array_merge($upData, $rpData);
        //如果用户没有自定义功能。修改数据为用户具有的角色的
        if (empty($function)) {
            return ErrCodeFacade::getJError(29);
        }
        return $this->jsonTable->successByData($function);
    }


    /**
     * 通过用户信息获取功能列表
     *
     * @param integer $appType 应用类型
     * @param integer $user 用户id
     * @param string $parentMenu 父级菜单编码
     * @return JsonTable data节点为菜单显示功能编码数组
     */
    public function getMenuByUser(int $appType, int $user, string $parnetMenu = null): JsonTable
    {
        try {
            $upMenu = [];
            // 获取菜单
            if (!($jResult = MenuLogic::instance()->getMenu(false, $parnetMenu, $appType))->isSuccess()) {
                return $jResult;
            }
            $menu = $jResult->data ?? [];
            // 获取用户权限
            if (!($jResult = $this->getByUser($user, $appType))->isSuccess()) {
                return $jResult;
            }
            $function = $jResult->data;
            //提取具有权限部分菜单
            foreach ($function as $val) {
                if (substr($val, -1, 2) == '00') {
                    $mcode = 'MN' . substr($val, 2, strlen($val) - 4);
                    foreach ($menu as $menuval) {
                        if ($menuval["mn_code"] === $mcode) {
                            $upMenu[$mcode] = $menuval;
                        }
                    }
                }
            }
            $flag = array_column($upMenu, 'mn_sort');
            array_multisort($flag, SORT_ASC, $upMenu);
            return MenuLogic::instance()->packageMenuByArray($upMenu, 'mn_');
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 角色功能查看
     *
     * @param integer $roleId 角色id 按照id查询相关类型的功能   fn_chose=0不具有权限fn_chose=1该角色具有权限
     * @param integer $userId 用户id
     * @param integer $appType 操作应用类型
     * @return JsonTable
     */
    public function roleFunction(int $roleId, int $userId = 0, int $appType = 3): JsonTable
    {
        try {
            //主查询的角色
            $role = RoleModel::find($roleId);
            if (\is_null($role)) {
                return ErrCodeFacade::getJError(25, ['name' => '角色信息']);
            }
            $roleAppType = $role->r_app_type;
            if (!($jResult = FunctionLogic::instance()->getFunction((int)$roleAppType))->isSuccess()) {
                return $jResult;
            }
            $allFunc = $jResult->data;
            if (!($jResult = MenuLogic::instance()->getMenu(intval($roleAppType), false))->isSuccess()) {
                return $jResult;
            }
            $allMenu = $jResult->data;
            $rpData = RolePrivilegeModel::instance()->getFunction($roleAppType, $roleId)->column('rp_function_code');

            if ($userId !== 0) {
                if (!($jResult = $this->getByUser($appType, $userId))->isSuccess()) {
                    return $jResult;
                }
                $upData = $jResult->data;
                $rpData = array_intersect($rpData, $upData);
            }
            $funclist = [];
            foreach ($allMenu as $key => $item) {
                $item = Helper::delPrefixArr($item, 'mn_');
                $mnCode = $item["code"];
                if (isset($allFunc[$mnCode])) {
                    $fun = $allFunc[$mnCode];
                    foreach ($fun as $funKey => $funItem) {
                        in_array($funKey, $rpData) ? $fun[$funKey]['chose'] = 1 : $fun[$funKey]['chose'] = 0;
                    }
                    $item['func'] = $fun;
                }
                $funclist[$key] = $item;
            }
            return MenuLogic::instance()->packageMenuByArray($funclist);
        } catch (\Throwable $ex) {
            Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }


    /**
     * 用户功能列表
     *
     * @param int $appType 应用类型
     * @param int $userId 用户id
     * @param int $roleId 角色id
     * @return JsonTable
     */
    public function userFunctionList(int $appType, int $userId, int $roleId = 0): JsonTable
    {
        try {
            //角色->功能->对应
            if ($roleId !== 0) {
                $roleObj = RoleModel::instance()->where('r_id', $roleId)->find();
                if (\is_null($roleObj)) {
                    return ErrCodeFacade::getJError(25, ['name' => '角色信息']);
                }
                //组装--数组
                $role = [$roleId];
            } //用户->角色关系->
            else {
                // 获取用户关联角色列表 --数组
                $role = RelationModel::instance()->getRoleByUser($appType, $userId)->column('rel_role');
            }

            $jAllFunc = FunctionLogic::instance()->getFunction($appType);
            if ($jAllFunc->isSuccess()) {
                $allFunc = $jAllFunc->data;
            }
            $jAllMenu = MenuLogic::instance()->getMenu($appType, false);
            if ($jAllMenu->isSuccess()) {
                $allMenu = $jAllMenu->data;
            }
            $rpDataAll = [];
            foreach ($role as $role_id) {
                $rpData = RolePrivilegeModel::instance()->getFunction($appType, $role_id)->column('rp_function_code');
                $rpDataAll = array_merge($rpData, $rpDataAll);
            }
            $upData = UserPrivilegeModel::instance()->getFunction($appType, $userId)->column('up_function_code');

            $funclist = [];
            foreach ($allMenu as $key => $item) {
                $item = Helper::delPrefixArr($item, 'mn_');
                $mnCode = $item["code"];
                if (isset($allFunc[$mnCode])) {
                    $fun = $allFunc[$mnCode];
                    foreach ($fun as $funKey => $funItem) {
                        if (in_array($funKey, $rpDataAll)) {
                            in_array(
                                $funKey,
                                $upData
                            ) ? $fun[$funKey]['chose'] = 1 : $fun[$funKey]['chose'] = 0;
                            $item['func'] = $fun;
                            $funclist[$key] = $item;
                        }
                    }
                }
            }
            return MenuLogic::instance()->packageMenuByArray($funclist);
        } catch (\Throwable $ex) {
            Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 功能按顺序组装树状结构
     *
     * @param array $fun 功能
     * @param integer $appType 应用类型
     * @return JsonTable
     */
    protected function packageMenuFromFuncode(int $appType, array $fun): JsonTable
    {
        $menuCode = [];
        foreach ($fun as $val) {
            if (substr($val, -1, 2) == '00') {
                $mcode = 'MN' . substr($val, 2, strlen($val) - 4);
                $menuCode[] = $mcode;
            }
        }
        $menu = MenuModel::instance()->where('mn_code', 'in', $menuCode)->where('mn_app_type', $appType)
            ->order('mn_sort asc')->select();
        return MenuLogic::instance()->packageMenu($menu);
    }

    /**
     * 提交编辑角色权限的功能code
     *
     * @param int $rid 角色id
     * @param array $funclist 角色新的所有的权限数据
     * @param array $oldFun 角色下的旧的权限数据
     * @return JsonTable
     */
    public function editRoleFunction(int $rid, array $funclist, $oldFun = null): JsonTable
    {
        try {
            $role = RoleModel::instance()->where('r_id', $rid)->find();
            if (\is_null($role)) {
                return ErrCodeFacade::getJError(25, ['name' => '角色信息']);
            }
            $appType = $role->r_app_type;
            $roleModel = RolePrivilegeModel::instance()->where('rp_role', $rid)->where('rp_app_type', $appType);
            //所有角色权限一起编辑
            if ($oldFun !== null) {
                $roleModel->where('rp_function_code', 'in', $oldFun);
            }
            $roleModel->delete();

            $data = [];
            foreach ($funclist as $k => $v) {
                $data[] = [
                    'rp_role'          => $rid,
                    'rp_function_code' => $v,
                    'rp_app_type'      => $appType,
                ];
            }
            RolePrivilegeModel::instance()->saveAll($data);
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }


    /**
     * 用户功能编辑 -- 选择范围-->选择用户，列出角色，提供编辑自定义功能入口按钮：列出选择角色下具有的功能。提供勾选功能的操作。后提交接口。。提交改角色的类型和选中的功能编码
     *
     * @param int $appType 应用类型
     * @param int $userId 用户id
     * @param array $funclist 权限列表
     * @param array|null $oldFun 旧权限
     * @return JsonTable
     */
    public function editUserFunction(int $appType, int $userId, array $funclist, ?array $oldFun = null): JsonTable
    {
        try {
            //找出来用户具有角色   找角色的功能。   功能按照角色划分？！  userright 的相关 relation -》user
            $data = [];
            foreach ($funclist as $k => $v) {
                $data[] = [
                    'up_user'          => $userId,
                    'up_function_code' => $v,
                    'up_app_type'      => $appType,
                ];
            }
            //所有角色权限一起编辑
            if ($oldFun === null) {
                UserPrivilegeModel::instance()->where('up_user', $userId)->delete();
            } else {
                UserPrivilegeModel::instance()->where('up_function_code', 'in', $oldFun)->delete();
            }
            UserPrivilegeModel::instance()->saveAll($data);
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }
}
