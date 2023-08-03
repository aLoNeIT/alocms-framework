<?php

declare(strict_types=1);

namespace alocms\extend\dict\facade;

use alocms\extend\dict\Dict as DictUtil;
use think\Facade;

/**
 * 字典处理类
 * @see dict\Dict
 * @mixin \dict\Dict
 * @method static static setProcessor(ProcessorInterface $processor) 设置字典处理器
 * @method static DictUtil getDict(int $id) 获取字典对象
 * @method static bool|string|array checkData(DictUtil $dict, int $curd, array $data, bool $batch = false) 校验数据
 * @method static Query select(DictUtil $dict, array $condition = [], ?array $order = null, ?string $fuzzy = null) 查询数据
 * @method static Query findByPrimaryKey(DictUtil $dict, string|int $id, ?array $order = null) 通过主键查询单条数据
 * @method static Query find(DictUtil $dict, array $condition = [], ?array $order = null) 查询单条数据
 * @method static Query update(DictUtil $dict, array $data, array $condition = []) 更新数据
 * @method static Query save(DictUtil $dict, array $data = []) 创建数据
 * @method static Query delete($dict, array $condition = []) 删除数据
 * @method static Query build( DictUtil $dict, int $curd, array $condition = [], ?array $order = null, ?string $fuzzy = null) 构建数据库查询
 */
class Dict extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return DictUtil::class;
    }
}
