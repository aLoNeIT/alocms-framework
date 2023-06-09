<?php

declare(strict_types=1);

namespace alocms\model;

use think\db\Query;
use think\model\relation\HasMany;

/**
 * 功能明细模型
 */
class FunctionDetail extends Base
{
    /** @inheritDoc */
    protected $table = '{$database_prefix}_function_detail';
    /** @inheritDoc */
    protected $pk = 'fd_id';
    /** @inheritDoc */
    protected $prefix = 'fd_';


    /**
     * 获取对应权限模型
     *
     * @return HasMany
     */
    public function functionModel(): HasMany
    {
        return $this->hasMany(FunctionModel::class, 'fn_code', 'fd_function_code');
    }



    /**
     * 通过mvc获取功能编码
     *
     * @param string $module 模块
     * @param string $controller 控制器
     * @param string $action 方法
     * @param integer $appType 应用类型
     * @return Query 返回Query对象以便接下来的处理
     */
    public function getCode(string $module, string $controller, string $action, int $appType): Query
    {
        return $this->field('fd_function_code')
            ->where($this->condAppType($appType))
            ->where('fd_module', $module)
            ->where('fd_controller', $controller)
            ->where('fd_action', $action);
    }
}
