<?php

namespace alocms\console\process;

use alocms\constant\Task as TaskConstant;
use alocms\logic\MQCommonTask as MQCommonTaskLogic;
use alocms\util\JsonTable;

class MQPublisher extends Base
{
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
