<?php

namespace alocms\logic;

use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\logic\Dict as DictLogic;
use alocms\model\Menu as MenuModel;
use alocms\util\CacheConst;
use alocms\util\Helper;
use alocms\util\JsonTable;

/**
 * 字典数据表CURD类
 */
class DictDefine extends Base
{
    /**
     * 字典数据
     *
     * @var array
     */
    protected $items = [];

    /**
     * 获取面向客户端的字典数据信息
     *
     * @param integer $dictId  字典id
     * @param integer $appType 应用类型
     *
     * @return array
     */
    protected function getDict(int $dictId, int $appType = 0): array
    {
        $dictData = [];
        // 内存中不存在字典数据
        $key = CacheConst::dictDefine($dictId, $appType);
        // 判断内存中是否有数据
        if (!isset($this->items[$key])) {
            if (!cache("?{$key}")) {
                // 缓存中也不存在，则从数据库中读取
                $dictData = DictLogic::instance()->getDict($dictId, $appType)->toArray(false);
                cache($key, $dictData, CacheConst::ONE_DAY);
            } else {
                $dictData = cache($key);
            }
            $this->items[$key] = $dictData;
        } else {
            $dictData = $this->items[$key];
        }
        return $dictData;
    }

    /**
     * 清理字典缓存
     *
     * @param integer $dictId 字典id
     *
     * @return JsonTable
     */
    public function clearDict(int $dictId, int $appType = 0): JsonTable
    {
        try {
            // 内存中不存在字典数据
            $key = CacheConst::dictDefine($dictId, $appType);
            if (isset($this->items[$key])) {
                unset($this->items[$key]);
            }
            \cache($key, null);
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 获取字典项列表
     *
     * @param integer $dictId  字典id
     * @param integer $appType 应用类型
     *
     * @return JsonTable 字典项数据写在data节点
     */
    public function getItemList(int $dictId, int $appType = 3): JsonTable
    {
        try {
            $dictData = $this->getDict($dictId, $appType);
            return $this->jsonTable->successByData($dictData);
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }
    /**
     * 通过uri获取字典项列表
     *
     * @param string $uri uri地址
     * @param integer $appType 应用类型
     * @return JsonTable 返回JsonTable对象，data节点是字典项数据集合
     */
    public function getItemListByUri(string $uri, int $appType = 3): JsonTable
    {
        try {
            // 根据uri查询对应的页面
            $menu = MenuModel::instance()->getByUri($uri, $appType)->find();
            if (\is_null($menu)) {
                return ErrCodeFacade::getJError(
                    25,
                    [
                        'name' => '菜单数据'
                    ]
                );
            }
            // 获取页面的字典id
            $id = $menu->page->p_dict;
            return $this->getItemList($id, $appType);
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }
}
