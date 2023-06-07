<?php

namespace alocms\library\console\cron;

use alocms\library\logic\File as FileLogic;
use alocms\library\util\Helper;
use alocms\library\util\JsonTable;

/**
 * 定时清理文件
 */
class ClearFile extends Base
{
    /**
     * 任务主体 清理文件
     *
     * User: bimo
     * Date: 2020/12/22 14:05
     *
     * @param array $info
     * @return JsonTable
     */
    public function doProcess(&$info): JsonTable
    {
        try {
            $result = FileLogic::instance()->deleteExpiredFiles();
            if (!$result->isSuccess()) {
                return $result;
            }
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            $exception = [
                'class' => \class_basename($ex),
                'state' => $ex->getCode(),
                'msg' => $ex->getMessage(),
            ];
            Helper::logListenCritical(static::class, '定时任务清理文件异常', $exception);
            $this->echoMess($exception);
            return $this->jsonTable->error($ex->getMessage());
        }
    }
}
