<?php

declare(strict_types=1);

namespace alocms;

use alocms\util\{CmsException, Helper};
use think\db\exception\DbException;
use think\exception\Handle;
use think\Response;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /** @inheritDoc */
    public function report(\Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
        // 判断是否PDOException错误，如果是则提取在记录数据库错误信息
        if ($exception instanceof DbException) {
            $data = $exception->getData();
            try {
                Helper::logListenCritical(static::class, __FUNCTION__ . ':sql', \json_encode($data, JSON_UNESCAPED_UNICODE));
            } catch (\Throwable $ex) {
            }
        }
    }

    /** @inheritDoc */
    public function render($request, \Throwable $ex): Response
    {
        // 其他错误交给系统处理
        $response = parent::render($request, $ex);
        // 处理返回的节点信息为标准结构信息
        $rspData = $response->getData();
        $data['state'] = $ex instanceof CmsException ? $ex->getCode() : 1;
        $data['msg'] = $ex->getMessage();
        if ($this->app->isDebug() && !\is_null($rspData)) {
            $data['data'] = $rspData;
        }
        $data = $request->isJson() ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);
        return $response->data($data);
    }
}
