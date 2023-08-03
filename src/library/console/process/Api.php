<?php

declare(strict_types=1);

namespace alocms\console\process;

use alocms\event\object\TaskInfo;
use alocms\util\CacheConst;
use alocms\util\CmsException;
use alocms\util\Helper;
use alocms\util\JsonTable;
use think\facade\Cache;
use think\facade\Event;

abstract class Api extends Base
{
    /**
     * 循环次数
     *
     * @var integer
     */
    protected $loopNum = 0;
    /**
     * 每次任务结束睡眠总时长
     *
     * @var integer
     */
    public $sleepTime = 30;
    /**
     * 是否互斥执行
     *
     * @var boolean
     */
    public $mutex = false;
    /**
     * 睡眠时间步进，用于控制每次睡眠时长
     *
     * @var integer
     */
    public $sleepStep = 5;
    /**
     * 内部缓存数据所使用的key名
     *
     * @var string
     */
    protected $key = 'ProcessApi';

    /**
     * 初始化
     *
     * @return void
     */
    protected function initialize(): void
    {
        $this->key = \class_basename(static::class);
        parent::initialize();
        //计算任务key完整名称
        $keyData = [];
        $serverName = \config('system.server_name', 'alocms');
        if (!is_null($serverName)) {
            $keyData[] = $serverName;
        }
        $keyData[] = $this->key;
        $this->key = \implode(':', $keyData);
    }

    /**
     * 执行cli任务主函数
     *
     * @return JsonTable
     */
    public function process(): JsonTable
    {
        $jResult = $this->jsonTable->success();
        try {
            $this->loopInitialize();
            // 判断是否需要互斥执行
            if ($this->mutex) {
                $redis = Cache::store('redis');
                $key = CacheConst::processMutexTask(\class_basename(static::class));
                $value = time() . Helper::randStr();
                try {
                    if (false !== $redis->setnx($key, $value, 30)) {
                        //获取需要处理的数据
                        $data = $this->getTask();
                    } else {
                        $data = false;
                    }
                } finally {
                    if ($value == $redis->get($key)) {
                        // 若获取的value一致，则删除
                        $redis->delete($key);
                    }
                }
            } else {
                $data = $this->getTask();
            }
            //如果无数据，则进入休眠策略
            if (false === $data) {
                //每10次才输出一次无数据，避免无数据时输出内容过多
                if (10 == ++$this->loopNum) {
                    $this->echoMess(lang('task_no_data'));
                    $this->loopNum = 0;
                }
                //使用步进模式反复唤醒，避免睡眠时间过长无法响应退出消息
                $count = $this->sleepTime / $this->sleepStep;
                for ($i = 0; $i < $count; $i++) {
                    pcntl_signal_dispatch();
                    if ($this->killed) {
                        return $jResult;
                    }
                    sleep($this->sleepStep);
                }
                //退出本次任务
                return $jResult;
            }
            $this->echoMess(lang('task_begin'));
            // 获取任务信息
            $info = $this->getInfo();
            // 如果不需要被监听，可以返回false
            if (false !== $info && !($jResult = Event::trigger('TaskBegin', new TaskInfo($info), true))->isSuccess()) {
                return $jResult;
            }
            //执行任务主体
            if (!($jResult = $this->doProcess($data, $info))->isSuccess()) {
                return $jResult;
            }

            $this->echoMess(lang('task_end'));
            // 只有执行成功才回调任务结束
            if (false !== $info && !($jResult = Event::trigger('TaskEnd', new TaskInfo($info), true))->isSuccess()) {
                return $jResult;
            }
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        } finally {
            $this->loopUninitialize();
        }
    }
    /**
     * 执行任务
     *
     * @param mixed $data 任务数据
     * @param array $info 任务信息，引用传递
     * @return JsonTable 返回执行结果，true为成功，array为错误数据
     */
    abstract protected function doProcess(&$data, array &$info): JsonTable;

    /**
     * 获取任务信息
     *
     * @return mixed 任务信息，返回false代表不需要记录任务信息
     */
    protected function getInfo()
    {
        return [
            'name' => $this->name,
            'date' => Helper::zeroOfDay(),
            'loop_num' => 0, // 每日最大执行次数,0不限制次数，非0代表限制次数
        ];
    }
    /**
     * 获取任务数据
     *
     * @return mixed 获取任务失败返回false，获取成功返回数组
     */
    protected function getTask()
    {
        //获取本次处理所需要的数据
        return false;
    }
}
