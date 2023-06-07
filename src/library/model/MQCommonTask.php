<?php

declare(strict_types=1);

namespace alocms\library\model;

use think\model\concern\SoftDelete;

/**
 * MQ通用任务
 */
class MQCommonTask extends Base
{
    use SoftDelete;

    protected $table = '{$database_prefix}_mq_common_task';

    protected $pk = 'mct_id';

    protected $prefix = 'mct_';

    protected $createTime = 'mct_create_time';
    protected $updateTime = 'mct_update_time';
    protected $deleteTime = 'mct_delete_time';

    protected $defaultSoftDelete = 0;
}
