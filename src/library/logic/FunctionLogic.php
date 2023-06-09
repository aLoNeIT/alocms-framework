<?php

declare(strict_types=1);

namespace alocms\logic;

use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\model\FunctionModel;
use alocms\model\Relation as RelationModel;
use alocms\model\RolePrivilege as RolePrivilegeModel;
use alocms\model\UserPrivilege as UserPrivilegeModel;
use alocms\util\Helper;
use alocms\util\JsonTable;
use think\model\Collection;

/**
 * 功能逻辑类
 */
class FunctionLogic extends Base
{
    /**
     * 获取功能列表
     *
     * @param integer $appType 应用类型
     * @return JsonTable
     */
    public function getFunction(int $appType = 1): JsonTable
    {
        $data = FunctionModel::instance()->baseAppTypeQuery($appType)->select();
        return $data->isEmpty() ? ErrCodeFacade::getJError(25) : $this->jsonTable->successByData(
            $this->packageFunction($data)
        );
    }

    /**
     * 根据功能模型数据集合组装树状结构
     *
     * @param Collection $data function 查询结构集合
     * @param bool $menuTree 是否返回树状结构
     * @return array
     */
    public function packageFunction(Collection &$data, bool $menuTree = true): array
    {
        $List = [];
        foreach ($data as $index => $row) {
            $fun = $row->toArray();
            $fun = Helper::delPrefixArr($fun, 'fn_');
            if ($menuTree) {
                $List[$row->fn_menu_code][$row->fn_code] = $fun;
            } else {
                $List[$row->fn_code] = $fun;
            }
        }
        return $List;
    }

    /**
     * 通过用户获取功能
     *
     * @param integer $appType 应用类型
     * @param integer $user 用户id
     * @return JsonTable
     */
    public function getByUser(int $appType, int $user): JsonTable
    {
        // 获取用户关联角色列表
        $role = RelationModel::instance()->getRoleByUser($appType, $user,)->column('rel_role');
        // 获取角色权限
        $rolePrivilegeModelSql = RolePrivilegeModel::instance()->getFunction($appType, $role)
            ->field('rp_function_code as fn_code')->buildSql(); //
        $userPrivilegeModelSql = UserPrivilegeModel::instance()->getFunction($appType, $user)
            ->field('up_function_code as fn_code')
            ->union($rolePrivilegeModelSql)->buildSql();
        $function = FunctionModel::instance()->alias('a')
            ->join($userPrivilegeModelSql . ' b', ' a.fn_code=b.fn_code ', 'right')
            ->where('fn_app_type', $appType)
            ->select()->toArray();
        $reFunc = [];
        foreach ($function as $value) {
            $reFunc[$value['fn_code']] = Helper::delPrefixArr($value, 'fn_');
        }
        //如果用户没有自定义功能。修改数据为用户具有的角色的
        if (empty($reFunc) === true) {
            return ErrCodeFacade::getJError(29);
        }
        return $this->jsonTable->successByData($reFunc);
    }
}
