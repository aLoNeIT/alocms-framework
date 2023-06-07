<?php

namespace alocms\library\console\common;

use alocms\library\console\traits\Jecho;

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
     * @var \alocms\library\util\JsonTable
     */
    protected $jsonTable = null;
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->jsonTable = app('JsonTable', [], true);
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
