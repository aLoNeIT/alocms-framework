<?php

namespace alocms\console\common;

use alocms\console\traits\Jecho;

/**
 * Console下的基类，每个模块都要继承自这里
 * @package app\console\common
 */
class Base
{
    use Jecho;

    /**
     * JsonTable对象
     *
     * @var \alocms\util\JsonTable
     */
    protected $jsonTable = null;
    /**
     * 全局单例应用对象
     *
     * @var \think\App
     */
    protected $app = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->app = app();
        $this->jsonTable = $this->app->make('JsonTable', [], true);
        $this->initialize();
    }

    /**
     * 初始化函数，子类重写
     */
    protected function initialize(): void
    {
        $this->name = class_basename(static::class);
    }
}
