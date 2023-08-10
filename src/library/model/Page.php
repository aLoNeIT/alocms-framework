<?php

declare(strict_types=1);

namespace alocms\model;

use think\db\Query;
use think\model\relation\BelongsTo;
use think\model\relation\HasMany;

/**
 * 页面模型
 */
class Page extends Base
{
    /** @inheritDoc */
    protected $table = '{$database_prefix}_page';
    /** @inheritDoc */
    protected $pk = 'p_id';
    /** @inheritDoc */
    protected $prefix = 'p_';

    /**
     * 页面子项
     *
     * @return HasMany
     */
    public function pageItem(): HasMany
    {
        return $this->hasMany(PageItem::class, 'pi_page', 'p_id');
    }
}
