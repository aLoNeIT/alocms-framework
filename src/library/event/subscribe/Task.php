<?php

declare(strict_types=1);

namespace alocms\library\event\subscribe;

use alocms\library\event\object\TaskInfo as TaskInfoObject;
use alocms\library\facade\ErrCode as ErrCodeFacade;
use alocms\library\model\TaskRecord as TaskRecordModel;
use alocms\library\util\Helper;
use alocms\library\util\JsonTable;
use think\facade\Db;

/**
 * Task事件订阅类
 */
class Task
{
    protected $eventPrefix = 'Task';

    protected $jsonTable = null;

    public function __construct()
    {
        $this->jsonTable = app('JsonTable', [], true);
    }
    /**
     * 任务开始
     *
     * @param TaskInfoObject $task 任务信息对象
     * @return JsonTable
     */
    public function onBegin(TaskInfoObject $task): JsonTable
    {
        $jResult = $this->jsonTable->success();
        try {
            $taskData = TaskRecordModel::where('tr_date', $task->date)
                ->where('tr_name', $task->name)->find();
            if (!\is_null($taskData)) {
                // 判断是否允许反复执行
                if ((0 == $task->loop_num) || ($task->loop_num > $taskData->tr_execute_num)) {
                    // 允许执行，更新数据
                    $taskData = $taskData->save([
                        'tr_begin_time' => time(),
                        'tr_state' => 1,
                    ]);
                } else {
                    // 超过执行次数限制
                    return ErrCodeFacade::getJError(5101, [
                        'num' => $task->loop_num,
                    ]);
                }
            } else {
                // 新数据，则创建
                $taskData = TaskRecordModel::create([
                    'tr_date' => $task->date,
                    'tr_name' => $task->name,
                    'tr_begin_time' => time(),
                ]);
            }
        } catch (\Throwable $ex) {
            $jResult = $this->jsonTable->error($ex->getMessage(), $ex->getCode());
            Helper::logListenCritical(static::class, __FUNCTION__ . ":{$ex->getMessage()}", $ex->getTrace());
        }
        return $jResult;
    }
    /**
     * 任务结束
     *
     * @param TaskInfoObject $task 任务信息对象
     * @return JsonTable
     */
    public function onEnd(TaskInfoObject $task): JsonTable
    {
        $jResult = $this->jsonTable->success();
        try {
            $taskData = TaskRecordModel::where('tr_date', $task->date)
                ->where('tr_name', $task->name)->find();
            if (\is_null($taskData)) {
                return ErrCodeFacade::getJError(25);
            }
            // 更新结束状态
            $taskData->save([
                'tr_state' => 2,
                'tr_end_time' => time(),
                'tr_execute_num' => Db::raw('tr_execute_num+1'),
            ]);
        } catch (\Throwable $ex) {
            $jResult = $this->jsonTable->error($ex->getMessage(), $ex->getCode());
            Helper::logListenCritical(static::class, __FUNCTION__ . ":{$ex->getMessage()}", $ex->getTrace());
        }
        return $jResult;
    }
}
