<?php

declare(strict_types=1);

namespace alocms\library\logic;

use alocms\library\util\Helper;
use alocms\library\util\JsonTable;

/**
 * 文件操作逻辑类
 */
class File extends Base
{
    /**
     * 删除过期文件
     *
     * @param integer $days 过期天数
     * @return JsonTable
     */
    public function deleteExpiredFiles(int $days = 90): JsonTable
    {
        try {
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }
}
