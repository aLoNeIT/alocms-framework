<?php

declare(strict_types=1);

namespace alocms\library\console\process;

use alocms\library\util\JsonTable;

/**
 * process测试代码
 */
class ProcessTest extends Api
{
    /** @inheritDoc */
    protected function doProcess(&$data, array &$info): JsonTable
    {
        $this->echoMess('测试Process进程执行');
        return $this->jsonTable->success();
    }
    /** @inheritDoc */
    protected function getTask()
    {
        return [
            'task' => 'Test',
        ];
    }
}
