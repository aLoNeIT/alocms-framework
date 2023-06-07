<?php

/**
 * 定时任务配置文件
 *
 * 秒 分 时 日 月 星期几
 * crontab 格式 * *  *  *  * *    => ["类"]
 */

return [
    /**
     * 配置信息类型，1从配置文件读取，2从数据库读取
     */
    'config_type' => 1,
    /**
     * 队列类型，1使用redis的list，2使用rabbitmq consume，3使用rabbitmq get
     */
    'queue_type' => env('crontask.queue_type', 1),
    /**
     * 队列名称
     */
    // 'queue_name'=>'queue:crontask',
    //当使用RabbitMQ的时候，读取以下的配置
    'queue_name' => 'alocms.queue.crontask',
    'exchange_name' => 'alocms.exchange.crontask',
    'type' => 'direct',
    'route_name' => 'alocms.route.crontask',
    'tag_name' => 'alocms.tag.crontask',
    'crontab' => [
        '0 * * * * *' => [
            '\\alocms\\library\\console\\cron\\ClearFile', //文件清理
        ],
    ],
];
