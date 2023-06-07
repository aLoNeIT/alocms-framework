<?php

namespace alocms\library\console\process;

use alocms\library\traits\RabbitMQ as RabbitMQTrait;
use alocms\library\util\Helper;
use alocms\library\util\JsonTable;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use think\facade\Cache;

/**
 * 定时任务消费者
 * @author aLoNe.Adams.K <alone@alonetech.com>
 * @date 2020-02-28
 */
class CronConsumer extends CronBase
{

    use RabbitMQTrait;

    /**
     * 最大执行次数
     *
     * @var integer
     */
    protected $maxExecuteNum = 1000;

    /** @inheritDoc */
    protected function initialize(): void
    {
        parent::initialize();
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
        $this->maxExecuteNum = \config('system.consumer.max_execute_num', 1000);
    }

    /** @inheritDoc */
    protected function doProcess(&$data, array &$info): JsonTable
    {
        try {
            //这里根据不同类型进行不同处理方式
            switch ($this->config['queue_type']) {
                case 2:
                    try {
                        $this->mq->consumer($this->getQueueName(), $this->tagName, function ($msg) {
                            $this->loopInitialize();
                            $channel = $msg->delivery_info['channel'];
                            $deliveryTag = $msg->delivery_info['delivery_tag'];
                            try {
                                //被标记为结束，则至少应该处理完本次请求
                                if ($this->killed) {
                                    $channel->basic_cancel($this->tagName);
                                }
                                //解码消息
                                $mqData = $this->mq->decodeMsg($msg->body);
                                $class = $mqData['class'];
                                try {
                                    // 类不存在，记录错误信息，越过
                                    if (\class_exists($class)) {
                                        $obj = new $class();
                                        $obj->run();
                                    } else {
                                        $errMsg = lang('cron_class_not_found', [
                                            'class' => $class,
                                        ]);
                                        Helper::logListenError(static::class, __FUNCTION__ . ":{$errMsg}");
                                    }
                                    // 确认消费
                                    $channel->basic_ack($deliveryTag);
                                    $this->echoMess(lang('cron_task_consumed', [
                                        'class' => $class,
                                    ]));
                                } finally {
                                    //无论执行结果如何，都将删除锁
                                    Cache::store('redis')->delete($this->getLockName($class));
                                }
                            } catch (\Throwable $ex) {
                                Helper::logListenException(static::class, 'consume', $ex, [
                                    'mq_msg' => $this->mq->decodeMsg($msg->body),
                                ]);
                                // 消费失败，立即拒绝
                                $channel->reject($deliveryTag, true);
                                // 是否自动结束
                                if ($this->autoTerminate) {
                                    throw $ex;
                                }
                            } finally {
                                $this->loopUninitialize();
                                if ($this->maxExecuteNum-- <= 0) {
                                    $this->echoMess(lang('consumer_execute_maximum', [
                                        'maximum' => \config('system.consumer.max_execute_num', 1000),
                                    ]));
                                    Helper::logListen(static::class, 'consume:定时任务消费者到达最大执行次数');
                                    $this->kill();
                                }
                            }
                        });
                    } catch (\Throwable $ex) {
                        Helper::logListenException(static::class, __FUNCTION__, $ex);
                        // 重新初始化MQ
                        $this->init();
                    }
                    break;
                case 3:
                case 1:
                    if (3 == $this->config['queue_type']) {
                        list($srcData, $callback) = $data;
                        $class = $srcData['class'];
                    } else if (1 == $this->config['queue_type']) {
                        $class = $data['class'];
                    }
                    try {
                        if (!\class_exists($class)) {
                            return $this->jsonTable->error(lang('cron_class_not_found', [
                                'class' => $class,
                            ]));
                        }
                        $obj = new $class();
                        $obj->run();
                    } finally {
                        //无论执行结果如何，都将删除锁
                        Cache::store('redis')->delete($this->getLockName($class));
                    }
                    if (3 == $this->config['queue_type']) {
                        //回调通知rabbitmq 已消费
                        $callback();
                    }
                    break;
            }
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }
    /** @inheritDoc */
    public function kill(): void
    {
        parent::kill();
        if (2 == $this->config['queue_type']) {
            // 只有当rabbitmq的consumer模式，才执行cancel，因为RabbitMQTrait内重写了该方法
            $this->cancel();
        }
    }

    /** @inheritDoc */
    protected function getTask()
    {
        try {
            switch ($this->config['queue_type']) {
                case 1: //redis list
                    $result = Cache::store('redis')->rPop($this->getQueueName());
                    break;
                case 2: //rabbitmq consume模式
                    //这里不做额外处理，因为使用rabbitmq consume模式
                    $result = true;
                    break;
                case 3:
                    $result = $this->mq->get($this->getQueueName());
                    if (\is_null($result[0])) {
                        $result = false;
                    }
                    break;
                default:
                    $result = false;
                    break;
            }
            return $result;
        } catch (AMQPConnectionClosedException $ex) {
            Helper::logListenException(static::class, __FUNCTION__, $ex);
            // 重新初始化MQ
            $this->init();
            return false;
        }
    }
}
