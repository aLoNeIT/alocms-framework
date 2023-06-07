<?php

//本文件只用于演示，并无配置作用
return [
    // 驱动方式
    'default' => 'redis',
    'stores' => [
        'redis' => [
            'type' => 'Redis',
            'alias' => 'redis', // 使用tp自带cache，该值为cache中的名称
            'queue' => [ // 队列信息
                'name' => \env('mqworker.queue_name', 'mqworker'),
            ],
        ],
        'rabbitmq' => [
            'type' => 'RabbitMQ',
            'host' => \env('rabbitmq.host', '172.17.0.1'),
            'port' => \env('rabbitmq.port', '5672'),
            'user' => \env('rabbitmq.user', 'guest'),
            'password' => \env('rabbitmq.password', 'guest'),
            'vhost' => \env('rabbitmq.vhost', '/'),
            'queue' => [
                'name' => \env('mqworker.queue_name', 'his.mqworker.queue'),
                'exchange' => \env('mqworker.queue_exchange', 'his.mqworker.exchange'),
                'route' => \env('mqworker.queue_route', 'his.mqworker.route'),
                'tag' => \env('mqworker.queue_tag', 'his.mqworker.tag'),
                'type' => \env('mqworker.queue_type', 'direct'),
                'qos' => \env('mqworker.queue_qos', 1),
            ],
        ],
    ],
];
