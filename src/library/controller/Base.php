<?php

declare(strict_types=1);

namespace alocms\controller;

use alocms\util\JsonTable;

/**
 * 控制器基类
 */
class Base extends BaseController
{
    /**
     * JsonTable格式数据，前后端交互唯一标准
     *
     * @var JsonTable
     */
    protected $jsonTable = null;
    /**
     * 初始化
     *
     * @return void
     */
    protected function initialize(): void
    {
        $this->jsonTable = $this->app->make('JsonTable', [], true);
    }
    /**
     * 根据JsonTable结构数据抛出异常
     *
     * @param string $msg 消息体
     * @param integer $state 错误码
     * @param array $data 扩展消息
     * @return void
     * @throws \think\exception\HttpResponseException
     */
    protected function jexception($msg = 'error', int $state = 1, $data = []): void
    {
        if ($this->request->isJson()) {
            $response = \json(
                $this->jsonTable->message($msg, $state, $data)->toArray()
            );
        } else {
            $response = \response(
                $this->jsonTable->message($msg, $state, $data)->toJson()
            );
        }
        \abort($response);
    }

    /**
     * 获取json格式数据返回
     *
     * @param string $msg 消息体
     * @param integer $state 状态码
     * @param array|object $data 扩展消息
     * @return string|array
     */
    protected function jecho($msg = 'success', int $state = 0, $data = [])
    {
        return $this->request->isJson()
            ? $this->jsonTable->message($msg, $state, $data)->toArray()
            : $this->jsonTable->message($msg, $state, $data)->toJson();
    }
    /**
     * 获取错误数据返回
     *
     * @param string $msg 消息体
     * @param integer $state 错误码
     * @param array|object $data 扩展消息
     * @return string|array
     */
    protected function jerror($msg = 'failed', int $state = 1, $data = [])
    {
        return $this->jecho($msg, $state, $data);
    }
    /**
     * 获取成功数据返回
     *
     * @param string $msg 消息体
     * @param array|object $data 扩展消息
     * @return string|array
     */
    protected function jsuccess($msg = 'success', $data = [])
    {
        return $this->jecho($msg, 0, $data);
    }
}
