<?php

declare(strict_types=1);

namespace alocms\model;

use think\db\Query;
use think\model\relation\BelongsTo;

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
}
