<?php

declare(strict_types=1);

namespace alocms\extend\dict\interface;

use alocms\extend\dict\util\Dict as DictUtil;
use think\Model;

/**
 * 字典处理接口
 * 
 * @author alone <alone@alonetech.com>
 */
interface Processor
{
    /**
     * 获取字典
     *
     * @param integer $id 字典id
     * @param integer $appType 应用类型
     * @param boolean $newInstance 是否返回新的对象
     * @return DictUtil 返回字典对象
     */
    public function getDict(int $id, int $appType = 0, bool $newInstance = false): DictUtil;
    /**
     * 获取模型
     *
     * @param DictUtil $dict 字典类
     * @param string $module 模块名
     * @return Model 返回模型对象
     */
    public function getModel(DictUtil $dict, string $module = 'common'): Model;
    /**
     * 获取表前缀
     *
     * @return string 返回系统表前缀
     */
    public function getTablePrefix(): string;
}
