<?php

namespace alocms\console\process;

use alocms\constant\Task as TaskConstant;
use alocms\logic\MQCommonTask as MQCommonTaskLogic;
use alocms\util\JsonTable;

/**
 * 通用任务发布
 * 测试用，没啥意义
 */
class MQPublisher extends Base
{
    /** @inheritDoc */
    public function process(): JsonTable
    {
        return MQCommonTaskLogic::instance()
            ->publish(TaskConstant::TYPE_TEST, '\alocms\facade\Test::say', [
                'one' => 1,
                'two' => 2,
                'class' => static::class,
                'time' => microtime(),
            ]);
    }
}
