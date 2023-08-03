<?php

namespace alocms\console\process;

use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\logic\MQCommonTask as MQCommonTaskLogic;
use alocms\util\JsonTable;

/**
 * 通用任务处理
 */
class MQCommonTask extends MQWorker
{
    /**
     * 内部缓存数据所使用的key名
     *
     * @var string
     */
    protected $key = 'MQCommonTask';

    /** @inheritDoc */
    protected function initialize(): void
    {
        // 读取MQ通用任务配置
        $config = \config('system.mq_common_task', [
            'driver' => 'redis',
            'queue' => [
                'name' => 'mq_common_task'
            ]
        ]);
        // 配置驱动，父类实例化
        $this->driver = $config['driver'];
        parent::initialize();
        // 设置队列信息
        $this->mqWorker->setQueueConfig($config['queue']);
    }

    /** @inheritDoc */
    protected function doProcess(&$data): JsonTable
    {
        if (!\is_array($data)) {
            return ErrCodeFacade::getJError(650);
        }
        return MQCommonTaskLogic::instance()->consume($data);
    }
}
