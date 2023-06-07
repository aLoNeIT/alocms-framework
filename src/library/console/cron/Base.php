<?php

namespace alocms\library\console\cron;

use alocms\library\console\common\Base as CommonBase;
use alocms\library\event\object\TaskInfo;
use alocms\library\util\CmsException;
use alocms\library\util\Helper;
use alocms\library\util\JsonTable;
use think\facade\Event;
use think\facade\Lang;

/**
 * 定时任务基类
 */
abstract class Base extends CommonBase
{
    protected function initialize(): void
    {
        parent::initialize();
        // 暂时注释，验证下是否有必要再加载一次
        //加载语言文件
        // $root = runtime_path();
        //蛋疼加入多语言，突然发现没意义。。。。
        // Lang::load($root . '/app/console/lang/zh-cn.php');
    }

    /**
     * 配合think\swoole的定时任务使用
     *
     * @return void
     */
    public function run(): void
    {
        try {
            $this->echoMess(lang('task_begin'));
            //获取定时任务数据
            $info = $this->getInfo();
            // 如果不需要被监听，可以返回false
            if (false !== $info && !($jResult = Event::trigger('TaskBegin', new TaskInfo($info), true))->isSuccess()) {
                // 触发事件
                // Helper::throwifJError(Event::trigger('TaskBegin', new TaskInfo($info), true));
                Helper::logListenError(static::class, $jResult->msg, $jResult->data);
                return;
            }
            //执行任务处理
            if (!($jResult = $this->doProcess($info))->isSuccess()) {
                Helper::logListenError(static::class, $jResult->msg, $jResult->data);
            }
            $this->echoMess(lang('task_end'));
            // 只有执行成功才回调任务结束
            if (false !== $info && !($jResult = Event::trigger('TaskEnd', new TaskInfo($info), true))->isSuccess()) {
                Helper::logListenError(static::class, $jResult->msg, $jResult->data);
                return;
            }
        } catch (\Throwable $ex) {
            Helper::logListenCritical(static::class, __FUNCTION__ . ":{$ex->getMessage()}", $ex instanceof CmsException ? $ex->getData() : $ex->getTrace());
        }
    }

    /**
     * 获取任务信息
     *
     * @return array 返回任务信息
     */
    protected function getInfo(): array
    {
        return [
            'name' => $this->name,
            'date' => date('Ymd'),
            'loop_num' => 0, // 每日最大执行次数,0为不限制次数
        ];
    }

    /**
     * 定时任务执行主体
     *
     * @return JsonTable 返回JsonTable对象
     */
    abstract public function doProcess(&$info): JsonTable;
}
