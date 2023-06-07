<?php

namespace app\console\task;

class Test extends Base
{
    public function say($args)
    {
        //$args=func_get_args();
        $this->echoMess('hello world',1,$args);
    }
}