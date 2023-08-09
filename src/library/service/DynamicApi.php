<?php

declare(strict_types=1);

namespace alocms\service;

use think\Config;
use think\Route;
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
    }
    /**
     * 服务启动，支持注入
     *
     * @param Route $route 路由对象
     * @param Config $config 配置对象
     * @return void
     */
    public function boot(Route $route, Config $config): void
    {
        // 读取配置的动态路由处理接口
        $dynamicController = $config->get('alocms.route.dynamic_controller');
        if (!\is_null($dynamicController)) {
            // 注册路由
            $route->miss("{$dynamicController}@index", 'get');
            $route->miss("{$dynamicController}@save", 'post');
            $route->miss("{$dynamicController}@update", 'put');
            $route->miss("{$dynamicController}@delete", 'delete');
        }
    }
}
