<?php

declare(strict_types=1);

namespace alocms\util;

/**
 * 驱动基类
 * 
 * @author alone <alone@alonetech.com>
 */
class Driver
{
    /**
     * JsonTable对象
     *
     * @var JsonTable
     */
    protected $jsonTable = null;

    /**
     * 配置项
     *
     * @var array
     */
    protected $config = [];

    /**
     * 错误信息
     *
     * @var array
     */
    protected $errCode = [];

    public function __construct(array $config = [])
    {
        $this->jsonTable = app('JsonTable', [], true);
        $this->config = array_merge($this->config, $config);
        $this->initialize();
    }

    public function __destruct()
    {
        $this->uninitialize();
    }
    /**
     * 生成新实例
     *
     * @param array $config 配置信息
     * @param boolean $cover 是否覆盖原始配置
     * @return static 返回新实例
     */
    public function newInstance(array $config = [], bool $cover = false): static
    {
        return new static($cover ? $config : array_merge($this->config, $config));
    }

    /**
     * 设置配置信息
     *
     * @param array $config 配置信息
     * @param boolean $cover 是否覆盖，默认false
     * @return static 返回当前对象
     */
    public function setConfig(array $config, bool $cover = false): static
    {
        $this->config = $cover ? $config : array_merge($this->config, $config);
        return $this;
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
     * 反初始化，析构函数时执行
     *
     * @return void
     */
    protected function uninitialize(): void
    {
    }

    /**
     *  jecho 返回JsonTable格式的数组
     * 
     * @param integer $state 状态码
     * @param string $msg 消息内容
     * @param mixed|null $data 扩展消息
     * @return JsonTable
     */
    protected function jecho(int $state, string $msg, $data = null): JsonTable
    {
        return $this->jsonTable->message($msg, $state, $data);
    }



    /**
     * 返回JsonTable格式的数组
     * 
     * @param string $msg 消息内容
     * @param mixed|null $data 扩展消息
     * @return JsonTable
     */
    protected function jsuccess(string $msg = 'success', $data = null): JsonTable
    {
        return $this->jsonTable->success($msg, $data);
    }

    /**
     * 返回JsonTable格式的数组
     * 
     * @param string $msg 消息内容
     * @param integer $state 状态码
     * @param mixed|null $data 扩展消息
     * @return JsonTable
     */
    protected function jerror(string $msg, int $state = 1, $data = null): JsonTable
    {
        return $this->jsonTable->error($msg, $state, $data);
    }

    /**
     * 返回JsonTable格式的数组
     *
     * @param mixed|null $data 扩展数据
     * @return JsonTable
     */
    protected function jdata($data): JsonTable
    {
        return $this->jsonTable->successByData($data);
    }

    /**
     * 返回JsonTable格式数组
     *
     * @param integer $state 错误码
     * @param mixed|null $data  扩展消息
     * @return JsonTable
     */
    protected function jcode(int $state, $data = null): JsonTable
    {
        $state = \strval($state);
        $msg = $this->errCode[$state] ?? '未知错误';
        return $this->jsonTable->message($msg, $state, $data);
    }

    /**
     * 抛出异常
     *
     * @param integer|string $state 错误码
     * @param mixed|null $data 扩展消息
     * @param string|null $msg 错误信息
     * @return void
     * 
     * @throws CmsException
     */
    protected function raise($state, $data = null, ?string $msg = null): void
    {
        $msg = $this->errCode[\strval($state)] ?? (\is_null($msg) ? '未知错误' : $msg);
        throw new CmsException(\is_numeric($state) ? $msg : "{$msg}[{$state}]", \is_numeric($state) ? $state : 1, $data);
    }
}
