<?php

declare(strict_types=1);

namespace alocms\model;

use think\model\concern\SoftDelete;

/**
 * 任务执行记录模型
 */
class TaskRecord extends Base
{
    use SoftDelete;
    /** @inheritDoc */
    protected $table = '{$database_prefix}_task_record';
    /** @inheritDoc */
    protected $pk = 'tr_id';
    /** @inheritDoc */
    protected $prefix = 'tr_';
    /** @inheritDoc */
    protected $createTime = 'tr_create_time';
    /** @inheritDoc */
    protected $updateTime = 'tr_update_time';
    /** @inheritDoc */
    protected $deleteTime = 'tr_delete_time';
    /** @inheritDoc */
    protected $defaultSoftDelete = 0;
}
