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
        // 'think\Request' => Request::class,
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


    protected function initialize(): void
    {
        // 单独处理think\Request容器
        $request = $this->app->make('think\Request');
        if(! $request instanceof )
        // 初始化容器配置
        \array_walk($this->provider, function ($value, $key) {
            // 如果未配置该容器，则使用当前默认配置
            // 主要因为当前代码执行时机晚于TP框架
            if (!$this->app->has($key)) {
                $this->app->bind($key, $value);
            }
        });
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function getConfigPath(): string
    {
        return $this->rootPath . 'config' . DIRECTORY_SEPARATOR;
    }
}
