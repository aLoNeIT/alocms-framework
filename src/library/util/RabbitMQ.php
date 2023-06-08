<?php

namespace alocms\util;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPChannelClosedException;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * 基于php-amqplib的RabbitMQ工具类
 * 
 * @author alone <alone@alonetech.com>
 */
class RabbitMQ
{
    /**
     * 配置文件
     *
     * @var array
     */
    protected $options = [
        'host' => '',
        'port' => '',
        'user' => '',
        'password' => '',
        'vhost' => '',
        'insist' => false,
        'login_method' => 'AMQPLAIN',
        'locale' => 'en_US',
        'connection_timeout' => 3.0,
        'read_write_timeout' => 3.0,
        'keepalive' => false,
        'heartbeat' => 0,
        'channel_rpc_timeout' => 0.0,
    ];
    /**
     * MQ连接对象
     *
     * @var AMQPStreamConnection
     */
    protected $connection = null;
    /**
     * MQ的通信信道
     *
     * @var AMQPChannel
     */
    protected $channel = null;
    /**
     * 构造函数
     *
     * @param array $options MQ连接配置项
     */
    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }
    /**
     * 析构函数，用于释放对象时同步释放MQ连接
     */
    public function __destruct()
    {
        $this->close();
    }
    /**
     * 打开AMQP连接
     *
     * @param boolean $force 是否强制重连
     * @return static 返回当前对象
     */
    protected function open($force = false): static
    {
        if (is_null($this->connection) || !$this->connection->isConnected() || true === $force) {
            $this->connection = new AMQPStreamConnection(
                $this->options['host'],
                $this->options['port'],
                $this->options['user'],
                $this->options['password'],
                $this->options['vhost'],
                $this->options['insist'],
                $this->options['login_method'],
                null, // login_response
                $this->options['locale'],
                $this->options['connection_timeout'],
                $this->options['read_write_timeout'],
                null, // context
                $this->options['keepalive'],
                $this->options['heartbeat'],
                $this->options['channel_rpc_timeout'],
            );
        }
        return $this;
    }
    /**
     * 强制打开一个新连接
     *
     * @return static
     */
    public function reopen(): static
    {
        return $this->open(true);
    }
    /**
     * 获取连接状态
     *
     * @return boolean
     */
    public function isConnected(): bool
    {
        return $this->connection->isConnected();
    }
    /**
     * 关闭连接
     *
     * @return static
     */
    public function close(): static
    {
        !\is_null($this->channel) && $this->channel->close();
        !\is_null($this->connection) && $this->connection->close();
        return $this;
    }
    /**
     * 获取与MQ的通信通道
     *
     * @throws AMQPChannelClosedException
     * @return AMQPChannel
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
    /**
     * 定义交换机
     *
     * @param string $name 交换机名称
     * @param string $type 路由类型
     * @param boolean $passive 如果你希望查询交换机是否存在．而又不想在查询时创建这个交换机．设置此为true即可；如果交换机不存在,则会抛出一个错误的异常.如果存在则返回NULL
     * @param boolean $durable 是否持久化
     * @param boolean $autoDelete 是否通道关闭时自动删除交换机
     * @param boolean $nowait 是否执行后不等待立刻结束
     * @return static 返回当前对象
     */
    public function exchangeDeclare(
        string $name,
        string $type = 'direct',
        bool $passive = false,
        bool $durable = true,
        bool $autoDelete = false,
        bool $nowait = false,
    ): static {
        $channel = $this->getChannel();
        $channel->exchange_declare($name, $type, $passive, $durable, $autoDelete, $nowait);
        return $this;
    }
    /**
     * 定义队列
     *
     * @param string $name 队列名称
     * @param boolean $passive 如果你希望查询交换机是否存在．而又不想在查询时创建这个交换机．设置此为true即可；如果交换机不存在,则会抛出一个错误的异常.如果存在则返回NULL
     * @param boolean $durable 是否持久化
     * @param boolean $exclusive 排他队列，队列只由创建它的进程进行消费，进程关闭则队列销毁
     * @param boolean $autoDelete 是否通道关闭时自动删除交换机
     * @param boolean $nowait 是否执行后不等待立刻结束
     * @return static 返回当前对象
     */
    public function queueDeclare(
        string $name,
        bool $passive = false,
        bool $durable = true,
        bool $exclusive = false,
        bool $autoDelete = false,
        bool $nowait = false
    ): static {
        $channel = $this->getChannel();
        $channel->queue_declare($name, $passive, $durable, $exclusive, $autoDelete, $nowait);
        return $this;
    }
    /**
     * 队列绑定
     *
     * @param string $queue 队列名
     * @param string $exchange 交换机名
     * @param string $route 路由名
     * @return static 返回当前对象
     */
    public function queueBind(string $queue, string $exchange, string $route = ''): static
    {
        $channel = $this->getChannel();
        $channel->queue_bind($queue, $exchange, $route);
        return $this;
    }
    /**
     * 配置消费者消费数据负载能力
     *
     * @param integer $num 消费者同时能消费的数据数量
     * @return static 返回当前对象
     */
    public function queueQos(int $num = 1): static
    {
        $channel = $this->getChannel();
        $channel->basic_qos(null, $num, null);
        return $this;
    }
    /**
     * 发布消息
     *
     * @param mixed $data 待发送的消息
     * @param string $exchange 交换机名称
     * @param string $route 路由名称
     * @param array $properties 消息属性
     * @return static 返回当前对象
     */
    public function publish($data, string $exchange, string $route, array $properties = []): static
    {
        $channel = $this->getChannel();
        $data = $this->encodeMsg($data);
        if (!isset($properties['delivery_mode'])) {
            $properties['delivery_mode'] = AMQPMessage::DELIVERY_MODE_PERSISTENT;
        }
        if (!isset($properties['content_type'])) {
            $properties['content_type'] = 'text/plain';
        }
        $message = new AMQPMessage($data, $properties);
        $channel->basic_publish($message, $exchange, $route);
        return $this;
    }
    /**
     * 消费消息
     *
     * @param string $queue 队列名称
     * @param string $tag 标签名称
     * @param Callable  $callback 收到消息后的回调函数
     * @param boolean $autoAck 是否自动消息确认
     * @param boolean $exclusive 当前队列是否只由本进程进行消费
     * @param boolean $nowait 是否不等待立刻返回
     * @return void
     */
    public function consumer(
        string $queue,
        string $tag,
        callable $callback,
        bool $autoAck = false,
        bool $exclusive = false,
        bool $nowait = false
    ): void {
        $channel = $this->getChannel();
        $channel->basic_consume($queue, $tag, false, $autoAck, $exclusive, $nowait, $callback);
        //循环判断是否还存在回调，因为上方设置过回调，所以可以保证死循环处理
        //若想关闭死循环结束进程，可以通过调用$channel->basic_cancel($tag)来删除掉回调
        //当所有回调删除完毕，则循环结束
        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
    /**
     * 取消消费回调
     *
     * @param string $tag 按照标签取消回调
     * @return boolean 返回取消结果
     */
    public function cancel(string $tag): bool
    {
        $channel = $this->getChannel();
        $channel->basic_cancel($tag);
        return true;
    }
    /**
     * 获取单条消息
     *
     * @param string $queue 队列名称
     * @return array 返回消息内容及应答回调函数
     */
    public function get(string $queue): array
    {
        $channel = $this->getChannel();
        $message = $channel->basic_get($queue);
        if (!$message) {
            return [null, null];
        }
        $ack = function () use ($channel, $message) {
            $channel->basic_ack($message->delivery_info['delivery_tag']);
        };
        $data = $this->decodeMsg($message->body);
        return [$data, $ack];
    }

    /**
     * 编码发布到MQ中的消息
     *
     * @param mixed $msg 消息体
     * @return string 编码后的数据
     */
    public function encodeMsg($msg): string
    {
        return is_scalar($msg) ? $msg : 'alocms_serialize:' . serialize($msg);
    }

    /**
     * 解码MQ中的数据
     *
     * @param string $msg 消息体
     * @return mixed 返回解码后的数据
     */
    public function decodeMsg(string $msg)
    {
        return 0 === strpos($msg, 'alocms_serialize:')
            ? unserialize(substr($msg, 17)) : $msg;
    }
}
