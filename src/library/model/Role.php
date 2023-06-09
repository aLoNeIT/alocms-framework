<?php

declare(strict_types=1);

namespace alocms\model;

use alocms\util\Helper;
use think\db\Query;
use think\model\concern\SoftDelete;

/**
 * 角色模型
 */
class Role extends Base
{
    use SoftDelete;
    /** @inheritDoc */
    protected $table = '{$database_prefix}_role';
    /** @inheritDoc */
    protected $pk = 'r_id';
    /** @inheritDoc */
    protected $prefix = 'r_';
    /** @inheritDoc */
    protected $createTime = 'r_create_time';
    /** @inheritDoc */
    protected $updateTime = 'r_update_time';
    /** @inheritDoc */
    protected $deleteTime = 'r_delete_time';
    /** @inheritDoc */
    protected $defaultSoftDelete = 0;
}
