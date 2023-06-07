<?php

declare(strict_types=1);

namespace alocms\library\constant;

/**
 * 任务常量
 */
class Task
{
    /**
     * 等待执行
     */
    const WAITING = 1;
    /**
     * 执行中
     */
    const PROCESSING = 2;
    /**
     * 执行成功
     */
    const SUCCEED = 3;
    /**
     * 执行失败
     */
    const FAILED = 4;

    // 任务类型
    const TYPE_TEST = 1;
}
