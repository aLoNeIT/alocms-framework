<?php

namespace alocms\library\console\process;

use alocms\library\constant\Task as TaskConstant;
use alocms\library\logic\MQCommonTask as MQCommonTaskLogic;
use alocms\library\util\JsonTable;

class MQPublisher extends Base
{
    public function process(): JsonTable
    {
        return MQCommonTaskLogic::instance()
            ->publish(TaskConstant::TYPE_TEST, '\alocms\library\facade\Test::say', [
                'one' => 1,
                'two' => 2,
                'class' => static::class,
                'time' => microtime(),
            ]);
    }
}
