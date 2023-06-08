<?php

namespace alocms\console\cron;

use alocms\util\Helper;
use alocms\util\JsonTable;

/**
 * 定时清理文件
 */
class CronTest extends Base
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
