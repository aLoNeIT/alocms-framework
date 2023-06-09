<?php

declare(strict_types=1);

namespace alocms\model;

use think\Model;

/**
 * 字典项模型
 */
class DictItem extends Model
{
    /** @inheritDoc */
    protected $table = '{$database_prefix}_dict_item';
    /** @inheritDoc */
    protected $pk = 'di_id';
    /** @inheritDoc */
    protected $prefix = 'di_';
}
