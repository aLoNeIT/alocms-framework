<?php

declare(strict_types=1);

namespace alocms\middleware;

use alocms\Request;
use alocms\util\{JsonTable, CmsException, Helper};
use think\Response;

/**
 * 中间件基类
 * @author 王阮强 <wangruanqiang@youzhibo.cn>
 * @date 2020-10-15
 */
class Base
{
    /**
     * 请求对象
     *
     * @var think\Request
     */
    protected $request = null;
    /**
     * JsonTable对象
     *
     * @var JsonTable
     */
    protected $jsonTable = null;
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->initialize();
    }
    /**
     * 初始化
     *
     * @return void
     */
    protected function initialize(): void
    {
        $this->jsonTable = app('JsonTable', [], true);
    }
    /**
     * 中间件处理方法
     *
     * @param Request $request 请求对象
     * @param \Closure $next 下一个处理函数
     * @return Response 返回响应对象
     */
    public function handle(Request $request, \Closure $next): Response
    {
        try {
            $this->request = $request;
            //执行前置方法
            $jResult = $this->before($request);
            if ($jResult->isSuccess()) {
                //成功才执行之后的方法
                $response = $next($request);
            } else {
                $response = \json($jResult->toArray());
            }
            //执行后置方法
            $jResult = $this->after($request, $response);
            if (!$jResult->isSuccess()) {
                $response = \json($jResult->toArray());
            }
        } catch (\Throwable $ex) {
            $response = $ex instanceof CmsException
                ? \json(Helper::jtable($ex->getCode(), $ex->getMessage(), $ex->getData()))
                : \json(Helper::jerror($ex->getMessage()));
            Helper::logListenCritical(static::class, __FUNCTION__ . ":{$ex->getMessage()}", $ex->getTrace());
        }
        return $response;
    }
    /**
     * 中间件前置方法，该方法返回的JsonTable对象中的data节点需要包含serviceInfo相关信息
     *
     * @param Request $request 请求对象
     * @return JsonTable
     */
    protected function before(Request $request): JsonTable
    {
        return $this->jsonTable->success();
    }
    /**
     * 中间件后置方法，若需要在后置方法中对响应数据进行修改，需要中间件自行修改
     *
     * @param Request $request 请求对象
     * @param Response $response 响应对象
     * @return JsonTable
     */
    protected function after(Request $request, Response $response): JsonTable
    {
        return $this->jsonTable->success();
    }
}
