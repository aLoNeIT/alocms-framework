<?php

declare(strict_types=1);

namespace alocms\model;

use think\model\concern\SoftDelete;

/**
 * MQ通用任务模型
 */
class MQCommonTask extends Base
{
    use SoftDelete;

    /** @inheritDoc */
    protected $table = '{$database_prefix}_mq_common_task';
    /** @inheritDoc */
    protected $pk = 'mct_id';
    /** @inheritDoc */
    protected $prefix = 'mct_';
    /** @inheritDoc */
    protected $createTime = 'mct_create_time';
    /** @inheritDoc */
    protected $updateTime = 'mct_update_time';
    /** @inheritDoc */
    protected $deleteTime = 'mct_delete_time';
    /** @inheritDoc */
    protected $defaultSoftDelete = 0;
}
