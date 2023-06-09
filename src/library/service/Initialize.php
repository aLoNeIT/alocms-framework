<?php

declare(strict_types=1);

namespace alocms\service;

use alocms\AloCms;
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
        $this->app->bind('alocms', AloCms::class);
    }

    public function boot(): void
    {
    }
}
