<?php

declare(strict_types=1);

namespace alocms\model;

use think\db\Query;
use think\model\relation\BelongsTo;

/**
 * 页面子项模型
 */
class PageItem extends Base
{
    /** @inheritDoc */
    protected $table = '{$database_prefix}_page_item';
    /** @inheritDoc */
    protected $pk = 'pi_id';
    /** @inheritDoc */
    protected $prefix = 'pi_';
}
