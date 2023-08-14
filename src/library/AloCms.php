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
        'install' => [ // 安装配置
            'disable_json' => false, // 是否需禁用json字段，false代表支持，true代表不支持，默认替换json为varchar(max)，如果是string类型则替换成填写的配置
        ],
        'route' => [ // 路由配置
            'miss' => [ // 动态控制器配置，全局miss 路由至该控制器进行处理
                'get' => '\\alocms\\controller\\DynamicApi@index',
                'post' => '\\alocms\\controller\\DynamicApi@save',
                'put' => '\\alocms\\controller\\DynamicApi@update',
                'delete' => '\\alocms\\controller\\DynamicApi@delete',
            ],
            'rules' => [ // 字典控制器配置，domain/dict 会路由至该控制器进行处理
                'dict/:id' => [
                    'method' => 'get',
                    'controller' => '\\alocms\\controller\\Dict@read',
                ],
                'dict/uri/:uri' => [
                    'method' => 'get',
                    'controller' => '\\alocms\\controller\\Dict@uri_read',
                ]
            ]
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
