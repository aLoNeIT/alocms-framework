<?php

declare(strict_types=1);

namespace alocms\library\traits;

use alocms\library\util\RabbitMQ as RabbitMQUtil;

trait RabbitMQ
{
    /**
     * mq连接对象
     *
     * @var object
     */
    protected $mq = null;

    /**
     * 交换机名称
     *
     * @var string
     */
    protected $exchangeName = 'exchange';

    /**
     * 队列名称
     *
     * @var string
     */
    protected $queueName = 'queue';

    /**
     * 路由名称
     *
     * @var string
     */
    protected $routeName = 'route';
    /**
     * 消费标签
     *
     * @var string
     */
    protected $tagName = 'tag';

    /**
     * 消息发送方式
     *
     * @var string
     */
    protected $type = 'direct';

    /**
     * 遇到异常是否自动终止
     *
     * @var boolean
     */
    protected $autoTerminate = true;

    /**
     * 初始化，子类有强自定义需求可以重写该方法
     *
     * @return void
     */
    protected function init()
    {
        $config = $this->getConfig();
        $this->mq = new RabbitMQUtil($config);
        $this->initMQ();
    }

    /**
     * 获取配置文件
     *
     * @return Array
     */
    protected function getConfig()
    {
        $config = config('rabbitmq', [
            'host' => '127.0.0.1',
            'port' => '5672',
            'user' => 'guest',
            'password' => 'guest',
            'vhost' => '/',
            'common_task' => [
                'exchange' => 'alocms.worker.common.task',
                'queue' => 'alocms.worker.common.task',
                'key_route' => 'alocms.worker.common.task',
                'tag_name' => 'alocms.worker.common.task',
                'type' => 'direct',
            ],
        ]);
        return $config;
    }

    /**
     * 初始化MQ内部信息
     *
     * @return void
     */
    protected function initMQ()
    {
        //初始化交换机
        $this->mq->exchangeDeclare($this->exchangeName, $this->type);
        //初始化队列
        $this->mq->queueDeclare($this->queueName);
        //队列绑定交换机
        $this->mq->queueBind($this->queueName, $this->exchangeName, $this->routeName);
        //定义同时能消费的数量
        $this->mq->queueQos(1);
    }
    /**
     * 结束进程处理
     *
     * @return void
     */
    public function cancel()
    {
        //取消mq消费回调
        $this->mq->cancel($this->tagName);
    }
}
