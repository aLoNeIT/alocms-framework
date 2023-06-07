<?php

return [
    /**
     * 服务器名称，不同服务器名称不一致
     * 需要注意的是生产部署时候
     */
    'server_name' => 'alocms',
    'project_name' => 'alocms', //项目名称
    // 代理服务器地址，用于request->ip()识别代理服务器
    'proxy_server' => '127.0.0.1,::1',
    'ip_white_list' => '0.0.0.0', // ip白名单
    // 登陆后创建的token信息
    'token' => [
        'expires_in' => 86400, //access_token有效时间，秒
        'refresh_expires_in' => 86200, //refresh_token有效时间，秒，要小于expires_in
    ],
    'app_type' => [ // 应用类型对应的模块名
        'admin' => 1,
    ],
    'database_prefix' => 'sys_',
];