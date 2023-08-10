<?php

declare(strict_types=1);

namespace alocms\logic;

use alocms\extend\dict\util\Dict as DictUtil;
use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\logic\Dict as DictLogic;
use alocms\logic\Session as SessionLogic;
use alocms\model\Menu as MenuModel;
use alocms\util\CmsException;
use alocms\util\Helper;
use alocms\util\JsonTable;

class Dynamic extends Base
{
    /**
     * 获取当前动态页面列表页字典
     *
     * @param string $uri
     * @return DictUtil
     */
    public function getSelectDict(string $uri): DictUtil
    {
        $request = \request();
        // 读取当前菜单对应的字典
        $dict =  $this->getDictByUri($request->baseUrl());
        return new DictUtil();
    }

    /**
     * 根据uri获取字典
     *
     * @param string $uri uri地址
     * @param integer $appType 应用类型
     * @return JsonTable 返回JsonTable对象，data节点是Dict对象
     * @todo 考虑将该字典缓存起来
     */
    public function getDictByUri(string $uri, int $appType = 3): JsonTable
    {
        try {
            // 根据uri查询对应的页面
            $menu = MenuModel::instance()->getByUri($uri, $appType)->find();
            if (\is_null($menu)) {
                ErrCodeFacade::getJError(
                    25,
                    [
                        'name' => '菜单数据'
                    ]
                );
            }
            // 获取页面的字典id
            $id = $menu->page->p_dict;
            $dict = DictLogic::instance()->getDict($id, $appType);
            // 根据页面子项保存的配置，调整当前字典内容
            /** @var \think\model\Collection $pageItems */
            $pageItems = $menu->page->pageItem;
            $fieldNames = [];
            // 获取当前用户的角色集合
            if (!($jResult = SessionLogic::instance()->getRole())->isSuccess()) {
                return $jResult;
            }
            $roleIds = \array_column($jResult->data, 'id');
            foreach ($pageItems as $pageItem) {
                // 检查当前字典项是否处于白名单内
                $whiteList = $pageItem->pi_role_whitelist;
                if (!\is_null($whiteList) && !empty($whiteList)) {
                    // 白名单存在且不为空，则进行检查
                    // 计算白名单的角色id集合与当前用户的角色id集合是否有交集
                    $result = \array_intersect($roleIds, $whiteList);
                    if (empty($result)) {
                        // 无交集则说明当前字典项不可展示
                        continue;
                    }
                }
                // 检查当前字典项是否处于黑名单内
                $blackList = $pageItem->pi_role_blacklist;
                if (!\is_null($blackList) && !empty($blackList)) {
                    // 黑名单存在且不为空，则进行检查
                    // 先计算黑名单的角色id集合与当前用户的角色id集合的差集
                    $result = \array_diff($roleIds, $whiteList);
                    // 再计算一次前面差集和当前用户角色id集合的交集，如果存在交集则证明用户角色id集合中存在非黑名单内的角色
                    $result = \array_intersect($result, $roleIds);
                    if (empty($result)) {
                        // 无交集则说明当前字典项不可展示
                        continue;
                    }
                }
                // 通过计算，当前字典项可展示
                $fieldNames[] = $pageItem->pi_dict_item_fieldname;
            }
            // 创建新的字典对象
            $dict = $dict->newInstance(empty($fieldNames) ? null : $fieldNames);
            return $this->jsonTable->successByData($dict);
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }
}
