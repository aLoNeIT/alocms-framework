<?php

declare(strict_types=1);

namespace alocms\service;

use think\facade\Route;
use think\Service;

/**
 * 动态接口服务，用于注册miss路由处理
 */
class DynamicApi extends Service
{
    /**
     * 服务注册
     *
     * @return void
     */
    public function register(): void
    {
        // 读取配置的动态路由处理接口
        $dynamicController = $this->app->config->get('alocms.dynamic_controller');
        if (!\is_null($dynamicController)) {
            // 注册路由
            Route::miss("{$dynamicController}/index", 'get');
            Route::miss("{$dynamicController}/save", 'post');
            Route::miss("{$dynamicController}/update", 'put');
            Route::miss("{$dynamicController}/delete", 'delete');
        }
    }

    public function boot(): void
    {
    }
}
