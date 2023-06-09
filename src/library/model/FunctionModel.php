<?php

declare(strict_types=1);

namespace alocms\model;

use think\db\Query;
use think\model\relation\BelongsTo;
use think\model\relation\HasMany;

/**
 * 功能模型
 */
class FunctionModel extends Base
{
    /** @inheritDoc */
    protected $table = '{$database_prefix}_function';
    /** @inheritDoc */
    protected $pk = 'fn_id';
    /** @inheritDoc */
    protected $prefix = 'fn_';

    /**
     * 获取功能明细
     *
     * @return HasMany
     */
    public function functionDetail(): HasMany
    {
        return $this->hasMany(FunctionDetail::class, 'fd_function_code', 'fn_code');
    }

    /**
     * 获取功能对应菜单
     *
     * @return BelongsTo
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'fn_menu_code', 'mn_code');
    }
}
