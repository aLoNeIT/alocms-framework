<?php

declare(strict_types=1);

namespace alocms\logic;

use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\model\Menu as MenuModel;
use alocms\util\Helper;
use alocms\util\JsonTable;
use alocms\util\YzbException;
use think\model\Collection;

class Menu extends Base
{

    /**
     * 校验是否有子集
     * @param string $field 字段名
     * @param string $code 编码
     * @param integer $appType 应用类型
     * @return integer 结果0-没有子集 munber-几个子集 -1--未查询到有效数据
     */
    public function haveChild(string $field, string $code, int $appType = 3): int
    {
        $parentcode = $code;
        if ($field == 'mn_id') {
            $menu = MenuModel::instance()->where([$field => $code])->find();
            if ($menu) {
                $parentcode = $menu->mn_code;
                $appType = $menu->mn_app_type;
            } else {
                return -1;
            }
        }
        $child = MenuModel::instance()->where(['mn_parent_code' => $parentcode, 'mn_app_type' => $appType])->count();
        return $child;
    }

    /**
     * 校验数据逻辑
     *
     * @param string $code 待检数据
     * @param int $appType 应用类型
     * @param int $type 类型 0-新建 其他-mn_id
     * @return JsonTable 返回JsonTable结果，data节点是菜单数据
     */
    public function checkData(string &$code, int $appType = 1, int $type = 0): JsonTable
    {
        try {
            $codeLength = strlen($code);
            $levelRule = $codeLength % 2;
            if ($levelRule != 0) {
                return ErrCodeFacade::getJError(702);
            }
            $haveMenu = MenuModel::instance()->where(['mn_code' => $code, 'mn_app_type' => $appType])->find();
            if ($haveMenu !== null) {
                return ErrCodeFacade::getJError(702);
            }
            $levelTemp = $codeLength / 2;
            $level = $levelTemp - 1;
            $data['level'] = $level;
            $data['path'] = substr($code, 0, 4);
            $data['parented'] = 0;
            $data['parent_code'] = '';
            if ($level > 1) {
                $data['parent_code'] = substr($code, 0, $codeLength - 2);;
                $pmenu = MenuModel::instance()->where(['mn_code' => $data['parent_code']])->find();
                if (!$pmenu) {
                    return ErrCodeFacade::getJError(705);
                }
                if ($type == 0 && $pmenu->mn_app_type != $appType) {
                    return ErrCodeFacade::getJError(706);
                }
                $child = $this->haveChild('mn_code', $data['parent_code'], $appType);
                if ($child > 0) {
                    $data['parented'] = 1;
                }
            }

            for ($i = 1; $level > $i; $i++) {
                $data['path'] .= '-' . substr($code, 0, 4 + $i * 2);
            }
            return $this->jsonTable->successByData($data);
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }


    /**
     * 获取菜单
     *
     * @param int $appType 菜单类型
     * @param bool $tree 是否组装成数组
     * @return JsonTable 返回结果集
     */
    public function getMenu(int $appType = 1, bool $tree = true, string $parnetMenu = null): JsonTable
    {
        try {
            $data = MenuModel::instance()->getDataByParent($appType, $parnetMenu)->order('mn_sort asc')->select();
            $parneInfo = MenuModel::find($parnetMenu);
            if ($tree) {
                if (!($jResult = $this->packageMenu($data, $parneInfo))->isSuccess()) {
                    return $jResult;
                }
                $menu = $jResult->data;
            } else {
                $menu = $data->toArray();
            }
            return $this->jsonTable->successByData($menu);
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 重组菜单成数组
     *
     * @param Collection $data 菜单模型数据集合
     * @param MenuModel|null $parneObj 父类对象
     * @return JsonTable
     */
    public function packageMenu(Collection &$data, MenuModel $parneObj = null): JsonTable
    {
        $menuData = [];
        $menuList = [];
        $level = 0;
        if (!is_null($parneObj)) {
            $level = $parneObj->mn_level;
        }
        $level = intval($level);
        $childenList = [];
        foreach ($data as $index => $row) {
            $menuArray = Helper::delPrefixArr($row->toArray(), 'mn_');
            if ($level + 3 == $row->mn_level) {
                $childenList[$row->mn_parent_code]['children'][$row->mn_code] = $menuArray;
            }
            if ($level + 1 == $row->mn_level) {
                $menuData[$row->mn_code] = $menuArray;
                $menuList[$row->mn_code] = &$menuData[$row->mn_code];
            } else {
                $menuList[$row->mn_parent_code]['children'][$row->mn_code] = $menuArray;
                $menuList[$row->mn_code] = &$menuList[$row->mn_parent_code]['children'][$row->mn_code];
                if ($level + 2 == $row->mn_level && isset($childenList[$row->mn_code])) {
                    if (!isset($menuList[$row->mn_code])) {
                        $menuList[$row->mn_code] = [];
                    }
                    $menuList[$row->mn_code]['children'] = $childenList[$row->mn_code]['children'];
                }
            }
        }
        return $this->jsonTable->successByData($menuData);
    }

    /**
     * 通过数组组装菜单结构
     *
     * @param array $data 菜单信息
     * @param null $prefix 前缀
     * @return JsonTable
     */
    public function packageMenuByArray(array &$data, $prefix = null): JsonTable
    {
        $menuData = [];
        $menuList = [];
        $codeName = 'code';
        $parentCodeName = 'parent_code';
        $levelName = 'level';
        if ($prefix !== null) {
            $codeName = $prefix . $codeName;
            $parentCodeName = $prefix . $parentCodeName;
            $levelName = $prefix . $levelName;
        }
        $childenList = [];
        foreach ($data as $index => $row) {
            $code = $row[$codeName];
            $parentCode = $row[$parentCodeName];
            $rowNew = Helper::delPrefixArr($row, 'mn_');

            if (3 == $row[$levelName]) {
                $childenList[$parentCode]['children'][$code] = $rowNew;
            }
            if (1 == $row[$levelName]) {
                $menuData[$code] = $rowNew;
                $menuList[$code] = &$menuData[$code];
            } else {
                $menuList[$parentCode]['children'][$code] = $rowNew;
                $menuList[$code] = &$menuList[$parentCode]['children'][$code];
                if (2 == $row[$levelName] && isset($childenList[$code])) {
                    if (!isset($menuList[$code])) {
                        $menuList[$code] = [];
                    }
                    $menuList[$code]['children'] = $childenList[$code]['children'];
                }
            }
        }

        return $this->jsonTable->successByData($menuData);
    }
}
