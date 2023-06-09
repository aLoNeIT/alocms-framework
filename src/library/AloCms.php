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
