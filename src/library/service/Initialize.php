<?php

declare(strict_types=1);

namespace alocms\service;

use alocms\AloCms;
use alocms\logic\Privilege as PrivilegeLogic;
use alocms\logic\Session as SessionLogic;
use think\Config;
use think\Lang;
use think\Route;
use think\Service;

/**
 * 启动服务，执行启动时必备的一些操作
 */
class Initialize extends Service
{
    /**
     * 容器绑定配置
     *
     * @var array
     */
    public $bind = [
        'alocms' => AloCms::class,
    ];
    /**
     * 服务注册
     *
     * @return void
     */
    public function register(): void
    {
        // 注册一些系统内使用的逻辑类
        $providers = [
            // 'SessionLogic' => SessionLogic::class,
            // 'PrivilegeLogic' => PrivilegeLogic::class,
        ];
        foreach ($providers as $name => $class) {
            if ($this->app->has($name)) continue;
            $this->app->bind($name, $class);
        }
    }

    public function boot(Route $route, Config $config, Lang $lang): void
    {
        /** @var AloCms $alocms */
        $alocms = $this->app->alocms;
        // 注册miss路由
        $miss = $config->get('alocms.route.miss', []);
        foreach ($miss as $method => $controller) {
            $route->miss($controller, $method);
        }
        // 注册通用路由
        $rules = $config->get('alocms.route.rules', []);
        foreach ($rules as $uri => $rule) {
            $route->{\strtolower($rule['method'])}($uri, $rule['controller']);
        }
        // 加载语言文件
        $lang->load($alocms->getRootPath('library/lang') . 'zh-cn.php');
    }
}
