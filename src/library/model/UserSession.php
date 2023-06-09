<?php

declare(strict_types=1);

namespace alocms\model;

use think\model\concern\SoftDelete;

/**
 * 用户会话模型
 */
class UserSession extends Base
{
    use SoftDelete;
    /** @inheritDoc */
    protected $table = '{$database_prefix}_user_session';
    /** @inheritDoc */
    protected $pk = 'us_id';
    /** @inheritDoc */
    protected $prefix = 'us_';
    /** @inheritDoc */
    protected $createTime = 'us_create_time';
    /** @inheritDoc */
    protected $updateTime = 'us_update_time';
    /** @inheritDoc */
    protected $deleteTime = 'us_delete_time';
    /** @inheritDoc */
    protected $defaultSoftDelete = 0;
}
