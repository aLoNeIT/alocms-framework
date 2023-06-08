<?php

namespace app\console\task;

use alocms\util\Helper;

class Test extends Base
{
    public function say($args)
    {
        Helper::logListenDebug(static::class, __FUNCTION__, $args);
    }
}
