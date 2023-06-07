<?php

declare(strict_types=1);

namespace alocms\extend\mqworker;

use alocms\library\util\Driver as DriverBase;
use alocms\library\util\JsonTable;

/**
 * MQWorker打工人驱动基类
 * @author 王阮强 <wangruanqiang@hongshanhis.com>
 * @date 2022-04-08
 */
abstract class Driver extends DriverBase
{
    /**
     * 编码后信息头
     */
    const PREFIX = 'mqworker_serialize:';

    /**
     * 配置项
     *
     * @var array
     */
    protected $config = [
        // 根据配置文件
        'type' => '',
    ];
    /**
     * 是否结束，用于退出消费循环
     *
     * @var boolean
     */
    protected $killed = false;

    /**
     * 错误信息
     *
     * @var array
     */
    protected $errCode = [
        // 内部错误
        '10' => '不支持的方法',
    ];

    /** @inheritDoc */
    protected function initialize(): void
    {
        parent::initialize();
        $this->setQueueConfig($this->config['queue'] ?? []);
    }

    /**
     * 发布数据
     *
     * @param mix $data 发布的数据
     * @return JsonTable 返回JsonTable对象
     */
    abstract public function publish($data): JsonTable;
    /**
     * 消费数据
     *
     * @param callable $callback 消费回调函数，函数参数为解码后的内容;返回值为0、1、2，参考Constant文件
     * @return void
     */
    abstract public function consume(callable $callback): void;
    /**
     * 取消消费
     *
     * @return void
     */
    public function cancel(): void
    {
        $this->killed = true;
    }
    /**
     * 设置队列配置信息
     *
     * @param array $config 队列配置信息
     * @return void
     */
    public function setQueueConfig(array $config): void
    {
        $this->config['queue'] = $config;
    }

    /**
     * 编码数据
     *
     * @param mix $data 源数据
     * @return string 返回编码后的字符串
     */
    protected function encodeMsg($data): string
    {
        return \is_scalar($data) ? $data : self::PREFIX . \serialize($data);
    }
    /**
     * 解码数据
     *
     * @param string $data 待解码的数据
     * @return mix 返回解码后的数据
     */
    protected function decodeMsg(string $data)
    {
        return 0 === \strpos($data, self::PREFIX)
            ? \unserialize(\substr($data, \strlen(self::PREFIX))) : $data;
    }
}
