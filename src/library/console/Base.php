<?php

declare(strict_types=1);

namespace alocms\library\console;

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
    protected $config = [
        'temp_path' => runtime_path(), //运行期临时目录
        'size' => 32, //该值不能小于task总数，且必须为2的倍数
        'timeout' => 60,
        'sleep_time' => 30,
        'sleep_step' => 50,
        'task' => [
            [
                // 'name' => 'CronPublisher', // 任务名称
                // 'class' => 'app\console\process\CronPublisher', // 任务类
                // 'worker_num' => 1, // 启动几个进程跑该任务
                // 'loop_num' => 1000, // 循环多少次重启
                // 'sleep_time' => 1, // 总计间隔多久执行一次
                // 'sleep_step' => 1, // 每多久检测一次是否该运行了
                // 'mutex' => false, // 是否互斥，true的时候多个进程只会有一个开始执行
            ],
        ]
    ]; //配置信息

    /**
     * 初始化
     *
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return void
     */
    protected function initialize(Input $input, Output $output): void
    {
        // 处理console临时目录问题
        $app = app();
        $app->setRuntimePath(\runtime_path('console'));
        /** @var \alocms\AloCms $alocms */
        $alocms = app('alocms');
        //蛋疼加入多语言，突然发现没意义。。。。
        Lang::load($alocms->getRootPath('library/console/lang') . 'zh-cn.php');
    }
}
