<?php

namespace alocms\library\console\process;

use alocms\library\traits\RabbitMQ as RabbitMQTrait;
use alocms\library\util\Helper;
use alocms\library\util\JsonTable;
use think\facade\Cache;
use XCron\CronExpression;

/**
 * 定时任务发布者
 */
class CronPublisher extends CronBase
{

    use RabbitMQTrait;

    /**
     * 最后一次清理时间
     *
     * @var integer
     */
    protected $lastClearTime = 0;

    /**
     * 任务是否执行的开关
     *
     * @var boolean
     */
    protected $switch = false;

    /**
     * 定时任务信息
     *
     * @var array
     */
    protected $cronTask = [
        [
            'key' => '* * * * * *', //cron表达式
            'val' => 'class', //需要执行的完整类名
            'next_time' => 155555555, //下次执行时间
            'cron' => null, //XCron\CronExpression对象
            'last_time' => 0, //上次执行时间
        ],
        [
            'key' => '* * * * * *', //cron表达式
            'val' => 'class', //需要执行的完整类名
            'next_time' => 155555555, //下次执行时间
            'cron' => null, //XCron\CronExpression对象
            'last_time' => 0, //上次执行时间
        ],
    ];

    /** @inheritDoc */
    protected function initialize(): void
    {
        parent::initialize();
        $this->cronTask = [];
        //根据队列类型初始化信息
        switch ($this->config['queue_type']) {
            case 2:
            case 3:
                $this->exchangeName = $this->config['exchange_name'] ?? 'exchange.crontask';
                $this->routeName = $this->config['route_name'] ?? 'route.crontask';
                $this->queueName = $this->config['queue_name'] ?? 'queue.crontask';
                $this->tagName = $this->config['tag_name'] ?? 'tag.crontask';
                $this->type = $this->config['type'] ?? 'direct';
                $this->init(); //初始化MQ
                break;
        }
        $this->initConfig();
    }

    /**
     * 初始化定时任务配置信息，可以改成从数据库读取
     *
     * @return void
     */
    protected function initConfig()
    {
        $config = $this->config['crontab'];
        // 读取timer配置信息
        $i = 0;
        foreach ($config as $key => $val) {
            $cron = CronExpression::factory($key);
            $time = $cron->getNextRunDate()->getTimestamp();

            if (!is_array($val)) {
                $val = [$val];
            }
            foreach ($val as $item) {
                $this->cronTask[$i]['key'] = $key;
                $this->cronTask[$i]['val'] = $item;
                $this->cronTask[$i]['next_time'] = $time;
                $this->cronTask[$i]['cron'] = $cron;
                $this->cronTask[$i]['last_time'] = 0;
                $i++;
            }
        }
    }

    /**
     * 发布任务，可以被继承用于换成其他发布方式，如MQ
     * 默认发布依赖于redis的list
     *
     * @param string $class 任务完整类名
     * @return void
     */
    protected function publish($class)
    {
        switch ($this->config['queue_type']) {
            case 1:
                $this->publishByRedis($class);
                break;
            case 2:
            case 3:
                $this->publishByMQ($class);
                break;
        }
    }

    /**
     * 使用redis的list发布任务
     *
     * @param string $class 任务完整类名
     * @return void
     */
    protected function publishByRedis($class)
    {
        Cache::store('redis')->lPush($this->getQueueName(), [
            'class' => $class,
            'publish_time' => time(),
        ]);
    }

    /**
     * 使用RabbitMQ来发布任务
     *
     * @param string $class 任务完整类名
     * @return void
     */
    protected function publishByMQ($class)
    {
        $this->mq->publish([
            'class' => $class,
            'publish_time' => time(),
        ], $this->exchangeName, $this->routeName);
    }

    /** @inheritDoc */
    protected function doProcess(&$data, array &$info): JsonTable
    {
        try {
            foreach ($this->cronTask as &$one) {
                if ($one['next_time'] <= time()) {
                    $class = $one['val'];
                    if (!\class_exists($class)) {
                        $this->echoMess(lang('cron_class_not_found', [
                            'class' => $class,
                        ]), 1);
                        continue;
                    }
                    $lockName = $this->getLockName($class);

                    try {
                        $cron = $one['cron'];
                        $one['next_time'] = $cron->getNextRunDate()->getTimestamp();
                        if (false === Cache::store('redis')->setnx($lockName, 1, 86400)) {
                            //抢占锁失败，则说明有任务在执行，跳过
                            $this->echoMess(lang('cron_task_running', [
                                'class' => $class,
                            ]), 1);
                            continue;
                        }
                        //发布任务到缓存中
                        $this->publish($class);
                        $one['last_time'] = time();
                        $this->echoMess(lang('cron_task_published', [
                            'class' => $class,
                        ]));
                    } catch (\Throwable $ex) {
                        //发生异常则删除锁
                        Cache::store('redis')->delete($lockName);
                        Helper::logListenException(static::class, __FUNCTION__, $ex);
                    }
                }
            }
            unset($one);
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }
    /** @inheritDoc */
    public function getTask()
    {
        return $this->switch = !$this->switch;
    }

    /** @inheritDoc */
    protected function loopInitialize(): void
    {
        // 3分钟执行一次循环
        if ($this->lastClearTime < time() - 180) {
            parent::loopInitialize();
            $this->lastClearTime = time();
        }
    }
    /** @inheritDoc */
    protected function loopUninitialize(): void
    {
        // 3分钟执行一次循环
        if ($this->lastClearTime < time() - 180) {
            parent::loopUninitialize();
            $this->lastClearTime = time();
        }
    }
}
