<?php

declare(strict_types=1);

namespace alocms;

use alocms\util\{JsonTable, ErrCode};
use alocms\{Request, ExceptionHandle};
use think\App;

/**
 * alocms类库，基于ThinkPHP构建
 * @author 王阮强 <alone@alonetech.com>
 */
final class AloCms
{
    /**
     * 配置信息，仅做展示用，业务层需要自行配置
     */
    private $config = [
        'route' => [ // 路由配置
            'dynamic_controller' => '\\alocms\\controller\\DynamicApi', // 动态控制器配置，全局miss 路由至该控制器进行处理
            'dict_controller' => '\\alocms\\controller\\Dict' // 字典控制器配置，domain/dict 会路由至该控制器进行处理
        ],
        'system' => [ // 系统配置
            'white_list' => [ // 接口请求白名单
                'session' => [], // 无需校验session的接口
                'privilege' => [] // 无需校验权限的接口
            ],
        ]
    ];

    /**
     * 基础路径
     * @var string
     */
    private $rootPath = null;
    /**
     * ThinkPHP的App实例
     *
     * @var App
     */
    protected $app = null;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->app->bind('alocms', $this);
        $this->rootPath = realpath(__DIR__) . DIRECTORY_SEPARATOR;
        $this->initialize();
    }

    /**
     * 初始化
     *
     * @return void
     */
    protected function initialize(): void
    {
        // 加载语言文件
        $this->app->lang->load($this->getRootPath('library/lang') . 'zh-cn.php');
    }

    /**
     * 获取根目录
     *
     * @param string $path 子路径，不带后缀分隔符
     * @return string 返回处理后的路径
     */
    public function getRootPath(string $path = ''): string
    {
        return $this->rootPath . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }

    /**
     * 获取配置文件目录
     *
     * @return string
     */
    public function getConfigPath(): string
    {
        return $this->rootPath . 'config' . DIRECTORY_SEPARATOR;
    }
}
