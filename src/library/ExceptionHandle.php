<?php

declare(strict_types=1);

namespace alocms;

use alocms\common\util\YzbException;
use Exception;
use think\db\exception\DbException;
use think\exception\Handle;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 * @author alone <alone@alonetech.com>
 */
class ExceptionHandle extends Handle
{
    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
        // 判断是否PDOException错误，如果是则提取在记录数据库错误信息
        if ($exception instanceof DbException) {
            $data = $exception->getData();
            try {
                $this->app->log->record(\json_encode($data, JSON_UNESCAPED_UNICODE), 'critical');
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 其他错误交给系统处理
        $response = parent::render($request, $e);
        // 处理返回的节点信息为标准结构信息
        $rspData = $response->getData();
        $data['state'] = $e instanceof YzbException ? $e->getCode() : 1;
        $data['msg'] = $e->getMessage();
        if (app()->isDebug() && !\is_null($rspData)) {
            $data['data'] = $rspData;
        }
        $data = $request->isJson() ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);
        return $response->data($data);
    }
}
