<?php

namespace alocms\library\console\process;

use alocms\library\facade\ErrCode as ErrCodeFacade;
use alocms\library\logic\MQCommonTask as MQCommonTaskLogic;
use alocms\library\util\JsonTable;

/**
 * 通用任务处理
 */
class MQCommonTask extends MQWorker
{

    protected $key = 'MQCommonTask';

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

    /**
     * 处理任务
     *
     * @param array $data 保存的数据
     * @return bool|array 成功返回true，失败返回JsonTable
     */
    protected function doProcess(&$data): JsonTable
    {
        if (!\is_array($data)) {
            return ErrCodeFacade::getJError(650);
        }
        return MQCommonTaskLogic::instance()->consume($data);
    }
}
