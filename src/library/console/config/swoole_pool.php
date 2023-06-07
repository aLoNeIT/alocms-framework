<?php

/**
 * swoole_pool执行任务配置文件
 * task 任务
 * name 进程名称
 * class 进程类名全称，因为代码内限制，class节点json_encode后不要超过1024字节
 * worker_num 启动进程数量
 * loop_num 进程启动后执行指定循环次数后关闭
 * sleep_time 进程每次任务完成后休眠时间
 * sleep_step 进程每次休眠步进时长
 * mutex 是否互斥，true的时候多个进程只会有一个开始执行
 */
return [
    'temp_path' => \runtime_path(), //运行期临时目录
    'size' => 32, //该值不能小于task总数，且必须为2的倍数
    'timeout' => 60,
    'sleep_time' => 30,
    'sleep_step' => 50,
    'task' => [
        // 定时任务发布者
        [
            'name' => 'CronPublisher',
            'class' => 'app\console\process\CronPublisher',
            'worker_num' => 1,
            'loop_num' => 1000,
            'sleep_time' => 1,
            'sleep_step' => 1,
            'mutex' => false,

        ],
        // 定时任务消费者
        [
            'name' => 'CronConsumer',
            'class' => 'app\console\process\CronConsumer',
            'worker_num' => 1,
            'loop_num' => 1000,
            'sleep_time' => 1,
            'sleep_step' => 1,
            'mutex' => false,

        ],
    ],
];
