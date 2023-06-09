<?php

declare(strict_types=1);

namespace alocms\logic;

use alocms\constant\Task as TaskConstant;
use alocms\model\MQCommonTask as MQCommonTaskModel;
use alocms\util\Helper;
use alocms\util\JsonTable;
use mqworker\Driver as MQWorkerDriver;
use mqworker\facade\MQWorker as MQWorkerFacade;

/**
 * MQ通用任务逻辑处理
 */
class MQCommonTask extends Base
{
    /**
     * 获取MQ打工者驱动对象
     *
     * @param array|null $config 配置文件
     * @return MQWorkerDriver MQ打工者驱动对象
     */
    public function getMQWorker(?array $config = null): MQWorkerDriver
    {
        // 读取MQ通用任务配置
        $config = $config ?? \config('system.mq_common_task', [
            'driver' => 'redis',
            'queue' => [
                // redis配置
                'name' => 'mq_common_task',
            ],
        ]);
        // 配置驱动，实例化
        /** @var MQWorkerDriver $mqWorker */
        $mqWorker = MQWorkerFacade::store($config['driver']);
        // 设置队列信息
        $mqWorker->setQueueConfig($config['queue']);
        return $mqWorker;
    }

    /**
     * 发布通用任务
     *
     * @param string $type 任务类型
     * @param string $action 执行的函数，为静态方法（Facade）
     * @param array $params 执行时所需的参数
     * @param string|null $name 任务名称
     * @param integer $appType 应用类型
     * @param integer $pk 应用类型对应主表的id
     * @param integer $admin 应用类型对应的用户表id
     * @param string $srcTable 发布任务的关联源表名
     * @param string $srcField 发布任务的关联源表主键字段
     * @param integer $srcId 发布任务的关联源表id
     * @return JsonTable 返回JsonTable对象，成功时msg节点为任务id
     */
    public function publish(
        string $type,
        string $action,
        array $params,
        ?string $name = null,
        int $appType = 1,
        int $pk = 0,
        int $admin = 0,
        string $srcTable = '',
        string $srcField = '',
        int $srcId = 0
    ): JsonTable {
        try {
            //插入任务数据
            $mqData = [
                'app_type' => $appType,
                'task_code' => $type,
                'pk' => $pk,
                'name' => \is_null($name) ? ($type . '_' . date('YmdHis') . '_' . Helper::randStr(4, 4)) : $name,
                'admin' => $admin,
                'action' => $action,
                'params' => $params,
                'state' => TaskConstant::WAITING,
                'src_table' => $srcTable,
                'src_field' => $srcField,
                'src_id' => $srcId,
            ];
            //初始化数据库内容
            $dbData = [];
            foreach ($mqData as $key => $value) {
                $dbData["mct_{$key}"] = $value;
            }
            $mqTask = MQCommonTaskModel::create($dbData);
            $mqData = array_merge($mqData, [
                'id' => $mqTask->mct_id,
                'create_time' => $mqTask->mct_create_time,
            ]);
            $this->getMQWorker()->publish($mqData);
            return $this->jsonTable->success($mqTask->mct_id);
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }
    /**
     * 任务消费
     *
     * @param array $data
     * @return JsonTable
     */
    public function consume(array $data): JsonTable
    {
        try {
            [
                'id' => $id,
                'action' => $action,
                'params' => $params,
            ] = $data;
            //更新任务表状态
            MQCommonTaskModel::update([
                'mct_state' => TaskConstant::PROCESSING,
                'mct_process_time' => time(),
            ], [
                'mct_id' => $id,
            ]);
            //开始执行任务
            if (!\is_callable($action)) {
                return $this->jsonTable->error(lang('function_not_callable'));
            }
            $jResult = \call_user_func($action, $params);
            if (!($jResult instanceof JsonTable)) {
                //若非JsonTable格式返回数据，则不做任何处理
                $jResult = $this->jsonTable->success();
            }
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
        // 最后更新
        try {
            MQCommonTaskModel::update([
                'mct_state' => $jResult->isSuccess() ? TaskConstant::SUCCEED : TaskConstant::FAILED,
                'mct_finish_time' => time(),
                'mct_result' => $jResult->data ?? [],
            ], [
                'mct_id' => $id,
            ]);
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
        return $jResult;
    }
}
