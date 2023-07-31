<?php

declare(strict_types=1);

namespace alocms\console;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Config;
use think\facade\Lang;

class Base extends Command
{
    /**
     * 保存所需要用到的配置信息
     *
     * @var array
     */
    protected $config = [];

    /** @inheritDoc */
    protected function initialize(Input $input, Output $output)
    {
        // 处理console临时目录问题
        $this->app->setRuntimePath(\runtime_path('console'));
        /** @var \alocms\AloCms $alocms */
        $alocms = $this->app->alocms;
        //蛋疼加入多语言，突然发现没意义。。。。
        Lang::load($alocms->getRootPath('library/console/lang') . 'zh-cn.php');
    }
}
