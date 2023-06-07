<?php

namespace alocms\library\console\process;

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
        $this->config = config('crontask', []);
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
