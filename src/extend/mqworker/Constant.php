<?php

declare(strict_types=1);

namespace alocms\extend\mqworker;

class Constant
{
    /**
     * 消费成功
     */
    const CONSUME_SUCCEED = 0;
    /**
     * 消费失败
     */
    const CONSUME_FAILED = 1;
    /**
     * 消费失败并重新入队
     */
    const CONSUME_FAILED_REQUEUE = 2;
}
