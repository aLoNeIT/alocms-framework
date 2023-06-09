<?php

declare(strict_types=1);

namespace alocms\logic;

use alocms\traits\Instance as InstanceTrait;
use alocms\util\CmsException;
use alocms\util\Helper;
use alocms\util\JsonTable;

/**
 * Logic模块下基类
 */
class Base
{

    use InstanceTrait;

    /**
     * 专用返回对象
     *
     * @var JsonTable
     */
    protected $jsonTable = null;
    /**
     * 全局单例应用对象
     *
     * @var \think\App
     */
    protected $app = null;

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * 初始化函数
     *
     * @return void
     */
    protected function initialize()
    {
        $this->app = app();
        $this->jsonTable = $this->app->make('JsonTable', [], true);
    }
    /**
     * 演示函数
     *
     * @return JsonTable
     */
    private function demo(): JsonTable
    {
        try {
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }
}
