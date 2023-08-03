<?php

declare(strict_types=1);

namespace alocms\extend\mqworker\driver;

use alocms\util\JsonTable;
use alocms\extend\mqworker\Constant;
use alocms\extend\mqworker\Driver;
use think\cache\driver\RedisCluster;
use think\facade\Cache;

/**
 * 基于Redis驱动的MQ打工人
 */
class Redis extends Driver
{
    /**
     * 当前执行中缓存的数据
     *
     * @var mixed
     */
    protected $data = null;
    /**
     * redis实例句柄
     *
     * @var \alocms\think\cache\driver\RedisCluster
     */
    protected $handler = null;
    /**
     * 配置信息
     *
     * @var array
     */
    protected $config = [
        'type' => 'Redis',
        'alias' => 'redis', // 基于tp框架的Cache，配置的类型名
        'sleep' => 1, //无数据情况下休眠时长，单位秒
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'select' => 0,
        'timeout' => 5,
        'expire' => 0,
        'persistent' => false,
        'prefix' => '',
        'tag_prefix' => 'tag:',
        'serialize' => [],
        'cluster' => false, //是否开启集群模式
        'queue' => [
            'name' => 'redis',
        ],
    ];

    protected function initialize(): void
    {
        parent::initialize();
        if (isset($this->config['alias']) && $this->config['alias']) {
            // 配置了有效的别名，则代表使用tp的redis
            $this->handler = Cache::store($this->config['alias']);
        } else {
            // 使用当前配置中的缓存设置进行redis实例化
            $this->handler = new RedisCluster($this->config);
        }
    }

    /** @inheritDoc */
    public function publish($data): JsonTable
    {
        // 左进右出
        $key = $this->config['queue']['name'];
        $value = $this->encodeMsg($data);
        $this->handler->lPush($key, $value);
        return $this->jsuccess();
    }

    /** @inheritDoc */
    public function consume(callable $callback): void
    {
        $this->killed = false;
        $sleep = $this->config['sleep'] * 1000000;
        $key = $this->config['queue']['name'];
        while (!$this->killed) {
            // 获取数据
            $data = $this->handler->rPop($key);
            if (false === $data) {
                // 无数据则休眠
                \usleep($sleep);
                continue;
            }
            // 回调返回false，则终止消费
            $this->data = $this->decodeMsg($data);

            try {
                // 使用闭包函数执行
                $result = \call_user_func($callback, $this->data);
                switch ($result) {
                    case Constant::CONSUME_FAILED_REQUEUE:
                        $this->publish($data);
                        break;
                    default:
                        break;
                }
            } catch (\Throwable $ex) {
                // ignore exception
            }
        }
    }
}
