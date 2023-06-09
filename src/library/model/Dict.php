<?php

declare(strict_types=1);

namespace alocms\model;

use think\db\Query;
use think\Model;
use think\model\relation\HasOne;

/**
 * 字典模型
 */
class Dict extends Model
{
    /** @inheritDoc */
    protected $table = '{$database_prefix}_dict';
    /** @inheritDoc */
    protected $pk = 'd_id';
    /** @inheritDoc */
    protected $prefix = 'd_';

    /**
     * 获取字典项
     *
     * @return HasOne
     */
    public function dictItem(): HasOne
    {
        return $this->hasOne(DictItem::class, 'di_dict');
    }
}
