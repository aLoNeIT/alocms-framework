<?php

declare(strict_types=1);

namespace alocms\library\logic;

use alocms\library\traits\Instance as InstanceTrait;
use alocms\library\util\CmsException;
use alocms\library\util\Helper;
use alocms\library\util\JsonTable;

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
        $this->jsonTable = app('JsonTable', [], true);
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
