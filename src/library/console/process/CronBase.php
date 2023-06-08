<?php

namespace alocms\console\process;

/**
 * 定时任务基类
 * 
 * @author 王阮强 <wangruanqiang@youzhibo.cn>
 * @date 2020-12-08
 */
abstract class CronBase extends Api
{
    /**
     * 睡眠时间
     *
     * @var integer
     */
    public $sleepTime = 1;
    /**
     * 睡眠步进
     *
     * @var integer
     */
    public $sleepStep = 1;

    /**
     * 配置信息
     *
     * @var array
     */
    protected $config = [];

    /**
     * 初始化
     *
     * @return void
     */
    protected function initialize(): void
    {
        parent::initialize();
        $this->config = config('crontask.', [
            /**
             * 配置信息类型，1从配置文件读取，2从数据库读取
             */
            'config_type' => 1,
            /**
             * 队列类型，1使用redis的list，2使用rabbitmq consume，3使用rabbitmq get
             */
            'queue_type' => 1,
            //当使用RabbitMQ的时候，读取以下的配置
            // 队列名称
            'queue_name' => 'alocms.queue.crontask',
            // 交换机名称
            'exchange_name' => 'alocms.exchange.crontask',
            // 消息发送方式
            'type' => 'direct',
            // 路由名称
            'route_name' => 'alocms.route.crontask',
            // tag名称
            'tag_name' => 'alocms.tag.crontask',
            'crontab' => [
                '* * * * * *' => [
                    '\\alocms\\library\\console\\cron\\CronTest', //文件清理
                ],
            ],
        ]);
    }

    /**
     * 获取锁名称
     *
     * @param string $class 完整类名
     * @return string
     */
    protected function getLockName($class): string
    {
        $pathInfo = \explode("\\", $class);
        if (empty($pathInfo[0])) {
            unset($pathInfo[0]);
        }
        $class = \implode('_', $pathInfo);
        return "lock:crontask:{$class}";
    }

    /**
     * 获取队列名称
     *
     * @return string
     */
    protected function getQueueName(): string
    {
        return $this->config['queue_name'];
    }
}
