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
        // 字典路由配置
        $dictController = $config->get('alocms.route.dict_controller');
        $route->get('dict/:id', "{$dictController}@read");
        $route->get('dict/uri/:uri', "{$dictController}@uri_read");
        /** @var AloCms $alocms */
        $alocms = $this->app->alocms;
        // 加载语言文件
        $lang->load($alocms->getRootPath('library/lang') . 'zh-cn.php');
    }
}
