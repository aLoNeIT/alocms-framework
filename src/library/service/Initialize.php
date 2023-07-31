<?php

declare(strict_types=1);

namespace alocms\service;

use alocms\AloCms;
use alocms\logic\Session as SessionLogic;
use think\Service;

/**
 * 启动服务，执行启动时必备的一些操作
 */
class Initialize extends Service
{
    /**
     * 服务注册
     *
     * @return void
     */
    public function register(): void
    {
        // 绑定AloCms类
        $this->app->bind('alocms', AloCms::class);
        // 注册一些系统内使用的逻辑类
        // $providers = [
        //     'SessionLogic' => SessionLogic::class
        // ];
        // foreach ($providers as $name => $class) {
        //     if ($this->app->has($name)) continue;
        //     $this->app->bind($name, $class);
        // }
    }

    public function boot(): void
    {
    }
}
