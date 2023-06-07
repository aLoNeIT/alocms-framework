<?php

namespace alocms\library\console\cron;

use alocms\library\util\Helper;
use alocms\library\util\JsonTable;

/**
 * 定时清理文件
 */
class Test extends Base
{
    /** @inheritDoc */
    public function doProcess(&$info): JsonTable
    {
        try {
            Helper::logListenDebug(static::class, 'hello world!', $info);
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }
}
