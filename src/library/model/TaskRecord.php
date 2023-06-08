<?php

declare(strict_types=1);

namespace alocms\model;

use think\model\concern\SoftDelete;

/**
 * 任务执行记录
 *
 * @author 王阮强 <wangruanqiang@youzhibo.cn>
 * @date 2020-12-11
 */
class TaskRecord extends Base
{
    use SoftDelete;

    protected $table = '{$database_prefix}_task_record';

    protected $pk = 'tr_id';

    protected $prefix = 'tr_';

    protected $createTime = 'tr_create_time';
    protected $updateTime = 'tr_update_time';
    protected $deleteTime = 'tr_delete_time';

    protected $defaultSoftDelete = 0;
}
