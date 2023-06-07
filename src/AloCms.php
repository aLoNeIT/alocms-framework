<?php

declare(strict_types=1);

namespace alocms;

use alocms\library\util\{JsonTable, ErrCode};
use alocms\library\{Request, ExceptionHandle};
use think\App;

/**
 * alocms类库，基于ThinkPHP构建
 * @author 王阮强 <alone@alonetech.com>
 */
class AloCms
{
    /**
     * 基础路径
     * @var string
     */
    protected $rootPath = null;
    /**
     * ThinkPHP的App实例
     *
     * @var App
     */
    protected $app = null;

    protected $provider = [
        'think\Request' => Request::class,
        'think\exception\Handle' => ExceptionHandle::class,
        'JsonTable' => JsonTable::class,
        'ErrCode' => ErrCode::class,
    ];

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->app->bind('alocms', $this);
        $this->rootPath = realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
        $this->initialize();
    }

    /**
     * 初始化
     *
     * @return void
     */
    protected function initialize(): void
    {
        $this->initConfig();
        // 单独处理think\Request容器，如果不是\alocms\library\Request实例，则替换
        $request = $this->app->make('think\Request');
        if (!$request instanceof Request) {
            $this->app->bind('think\Request', Request::class);
        }
        // 初始化容器配置
        \array_walk($this->provider, function ($value, $key) {
            // 如果未配置该容器，则使用当前默认配置
            // 主要因为当前代码执行时机晚于TP框架
            if (!$this->app->has($key)) {
                $this->app->bind($key, $value);
            }
        });
    }
    /**
     * 初始化配置文件
     *
     * @return void
     */
    private function initConfig(): void
    {
        // 获取配置文件路径
        $configPath = $this->getConfigPath();
        $files = [];
        // 搜索配置文件
        if (\is_dir($configPath)) {
            $files = \glob($configPath . '*.php');
        }
        // 遍历配置文件，如果已存在则不加载
        foreach ($files as $file) {
            $key = pathinfo($file, PATHINFO_FILENAME);
            if (!$this->app->config->has($key)) {
                $this->app->config->load($file, \pathinfo($file, PATHINFO_FILENAME));
            }
        }
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
