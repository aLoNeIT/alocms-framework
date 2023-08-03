<?php

declare(strict_types=1);

namespace alocms\console\process;

use alocms\util\Helper;
use alocms\util\JsonTable;
use alocms\extend\mqworker\Constant;
use alocms\extend\mqworker\facade\MQWorker as MQWorkerFacade;

/**
 * MQWorker打工人基类，不可实例化
 */
abstract class MQWorker extends Base
{
    /**
     * 最大执行次数
     *
     * @var integer
     */
    protected $maxExecuteNum = 1000;
    /**
     * MQWorker打工人
     *
     * @var mqworker\MQWorker
     */
    protected $mqWorker = null;
    /**
     * 遇到异常是否自动终止
     *
     * @var boolean
     */
    protected $autoTerminate = true;
    /**
     * 驱动类型
     *
     * @var string
     */
    protected $driver = 'redis';

    /** @inheritDoc */
    protected function initialize(): void
    {
        parent::initialize();
        $this->maxExecuteNum = \config('system.consumer.max_execute_num', 1000);
        $this->mqWorker = MQWorkerFacade::store($this->driver);
    }

    /** @inheritDoc */
    public function kill(): void
    {
        $this->mqWorker->kill();
        parent::kill();
    }

    /**
     * 进程任务执行主体
     */
    public function process(): JsonTable
    {
        try {
            $this->mqWorker->consume(function ($data) {
                $this->loopInitialize();
                try {
                    return $this->doProcess($data)->isSuccess()
                        ? Constant::CONSUME_SUCCEED
                        : Constant::CONSUME_FAILED_REQUEUE;
                } catch (\Throwable $ex) {
                    Helper::logListenException(static::class, 'consume', $ex);
                    // 是否自动结束
                    if ($this->autoTerminate) {
                        throw $ex;
                    }
                    // 消费失败，重新发布
                    return Constant::CONSUME_FAILED_REQUEUE;
                } finally {
                    $this->loopUninitialize();
                    if ($this->maxExecuteNum-- <= 0) {
                        $this->echoMess(lang('consumer_execute_maximum', [
                            'maximum' => \config('system.consumer.max_execute_num', 1000),
                        ]));
                        Helper::logListen(static::class, 'MQ消费者到达最大执行次数');
                        $this->kill();
                    }
                }
            });
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            //出现异常休眠，避免占用过多资源
            sleep(10);
            //重新实例化mq对象
            MQWorkerFacade::forgetDriver('redis');
            $this->mqWorker = MQWorkerFacade::store('redis');
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 消费者处理逻辑
     *
     * @param string|array $data mq中存储的数据
     * @return JsonTable 否则返回JsonTable
     */
    abstract protected function doProcess(&$data): JsonTable;
}
