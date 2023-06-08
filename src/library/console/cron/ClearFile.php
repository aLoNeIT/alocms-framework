<?php

namespace alocms\console\cron;

use alocms\logic\File as FileLogic;
use alocms\util\Helper;
use alocms\util\JsonTable;

/**
 * 定时清理文件
 */
class ClearFile extends Base
{
    /** @inheritDoc */
    public function doProcess(&$info): JsonTable
    {
        try {
            $result = FileLogic::instance()->deleteExpiredFiles();
            if (!$result->isSuccess()) {
                return $result;
            }
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }
}
