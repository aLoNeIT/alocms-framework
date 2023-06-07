<?php

declare(strict_types=1);

namespace alocms\library\event\listener;

use think\facade\Log as LogFacade;

/**
 * 日志监听类
 *
 * @author 王阮强 <wangruanqiang@youzhibo.cn>
 * @date 2020-11-04
 */
class Log
{
    /**
     * 返回值通用对象
     *
     * @var JsonTable
     */
    protected $jsonTable = null;

    public function __construct()
    {
        $this->jsonTable = app('JsonTable', [], true);
    }
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($param)
    {
        try {
            LogFacade::record($param, $param['level'] ?? 'info');
        } catch (\Exception $ex) {
            return $this->jsonTable->error($ex->getMessage());
        }
        return $this->jsonTable->success(\class_basename(static::class));
    }
}
