<?php

declare(strict_types=1);

namespace mqworker\driver;

use alocms\util\JsonTable;
use mqworker\Constant;
use mqworker\Driver;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPChannelClosedException;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * 基于RabbitMQ驱动的MQ打工人
 */
class RabbitMQ extends Driver
{
    /**
     * 配置信息
     *
     * @var array
     */
    protected $config = [
        'type' => 'RabbitMQ',
        'host' => '',
        'port' => '',
        'user' => '',
        'password' => '', // 密码不加密
        'vhost' => '',
        'insist' => false,
        'login_method' => 'AMQPLAIN',
        'locale' => 'en_US',
        'connection_timeout' => 3.0,
        'read_write_timeout' => 3.0,
        'keepalive' => true,
        'heartbeat' => 30,
        'channel_rpc_timeout' => 0.0,
        'queue' => [
            'exchange' => '', // 交换机名
            'name' => '', // 队列名
            'route' => '', // 路由名
            'tag' => '', // 标签名
            'type' => '', // 消息发送类型
            'qos' => 1, // 消费者每次拉取到本地的消息数量
        ],
    ];

    /**
     * MQ连接对象
     *
     * @var \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    protected $connection = null;
    /**
     * MQ的通信信道
     *
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    protected $channel = null;

    /** @inheritDoc */
    protected function initialize(): void
    {
    }
    /** @inheritDoc */
    protected function uninitialize(): void
    {
        parent::uninitialize();
        $this->close();
    }

    /**
     * 打开AMQP连接
     *
     * @param boolean $force 是否强制重连
     * @return void
     */
    protected function open($force = false): void
    {
        if (is_null($this->connection) || !$this->connection->isConnected() || true === $force) {
            $this->connection = new AMQPStreamConnection(
                $this->config['host'],
                $this->config['port'],
                $this->config['user'],
                $this->config['password'],
                $this->config['vhost'],
                $this->config['insist'],
                $this->config['login_method'],
                null, // login_response
                $this->config['locale'],
                $this->config['connection_timeout'],
                $this->config['read_write_timeout'],
                null, // context
                $this->config['keepalive'],
                $this->config['heartbeat'],
                $this->config['channel_rpc_timeout']
            );
            [
                'name' => $name,
                'type' => $type,
                'exchange' => $exchange,
                'route' => $route,
                'qos' => $qos,
            ] = $this->config['queue'];
            // 执行队列处理
            $channel = $this->getChannel();
            // 初始化交换机
            $channel->exchange_declare($exchange, $type, false, true, false);
            //初始化队列
            $channel->queue_declare($name, false, true, false, false, false);
            //队列绑定交换机
            $channel->queue_bind($name, $exchange, $route);
            //定义同时能消费的数量
            $channel->basic_qos(null, $qos, null);
        }
    }
    /**
     * 强制打开一个新连接
     *
     * @return void
     */
    public function reopen(): void
    {
        $this->close();
        $this->open(true);
    }
    /**
     * 关闭连接
     *
     * @return void
     */
    public function close(): void
    {
        !\is_null($this->channel) && $this->channel->close();
        $this->channel = null;
        !\is_null($this->connection) && $this->connection->close();
        $this->connection = null;
    }

    /**
     * 获取与MQ的通信通道
     *
     * @throws \PhpAmqpLib\Exception\AMQPChannelClosedException
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    protected function getChannel(): AMQPChannel
    {
        $this->open();
        if (is_null($this->connection)) {
            throw new AMQPChannelClosedException('未创建有效信道');
        }
        if (is_null($this->channel)) {
            $this->channel = $this->connection->channel();
        }
        return $this->channel;
    }

    /** @inheritDoc */
    public function setQueueConfig(array $config): void
    {
        parent::setQueueConfig($config);
        $this->reopen();
    }

    /** @inheritDoc */
    public function publish($data): JsonTable
    {
        $channel = $this->getChannel();
        $data = $this->encodeMsg($data);
        $properties = [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'content_type' => 'text/plain',
        ];
        $message = new AMQPMessage($data, $properties);
        [
            'exchange' => $exchange,
            'route' => $route,
        ] = $this->config['queue'];
        $channel->basic_publish($message, $exchange, $route);
        return $this->jsuccess();
    }

    /** @inheritDoc */
    public function consume(callable $callback): void
    {
        $this->killed = false;
        $channel = $this->getChannel();
        [
            'name' => $queue,
            'tag' => $tag,
        ] = $this->config['queue'];
        // 开始消费，传递一个内部的比饱含书
        $channel->basic_consume($queue, $tag, false, false, false, false, function (AMQPMessage $msg) use ($callback) {
            $deliveryChannel = $msg->getChannel();
            // 获取消费标签
            $deliveryTag = $msg->getDeliveryTag();
            // 解码数据
            $data = $this->decodeMsg($msg->body);
            try {
                // 使用闭包函数执行
                $result = \call_user_func($callback, $data);
                switch ($result) {
                    case Constant::CONSUME_FAILED: // 消费失败，不重新入队
                        $deliveryChannel->basic_reject($deliveryTag, false);
                        break;
                    case Constant::CONSUME_FAILED_REQUEUE: // 消费失败，重新入队
                        $deliveryChannel->basic_reject($deliveryTag, true);
                        break;
                    default:
                        $deliveryChannel->basic_ack($deliveryTag);
                        break;
                }
            } catch (\Throwable $ex) {
                // ignore exception
            }
        });
        //循环判断是否还存在回调，因为上方设置过回调，所以可以保证死循环处理
        //若想关闭死循环结束进程，可以通过调用$channel->basic_cancel($tag)来删除掉回调
        //当所有回调删除完毕，则循环结束
        // 这里不监听killed
        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    /**
     * 取消消费
     *
     * @return void
     */
    public function cancel(): void
    {
        parent::cancel();
        $channel = $this->getChannel();
        $channel->basic_cancel($this->config['queue']['tag']);
    }
}
