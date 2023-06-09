<?php

declare(strict_types=1);

namespace alocms\console\process;

use alocms\console\common\Base as CommonBase;
use alocms\think\cache\driver\RedisCluster;
use alocms\util\{JsonTable, Helper};
use think\console\Input;
use think\console\Output;
use think\facade\Cache;

/**
 * 后台任务处理基类
 */
abstract class Base extends CommonBase
{
    /**
     * 是否结束进程
     *
     * @var bool
     */
    protected $killed = false;
    /**
     * 进程池对象
     *
     * @var object
     */
    protected $pool = null;
    /**
     * 控制台输入对象
     *
     * @var Input
     */
    protected $input = null;
    /**
     * 控制台输出对象
     *
     * @var Output
     */
    protected $output = null;

    /**
     * 上次执行时间
     *
     * @var integer
     */
    protected $lastTime = 0;

    /**
     * 构造函数
     *
     * @param int $workerId 进程的序号
     * @param object $pool 进程池对象
     * #param string $name 进程名
     */
    public function __construct($workerId, $pool, $name)
    {
        $this->reset();
        $this->workerId = $workerId;
        $this->pool = $pool;
        parent::__construct();
        //因为父类会执行initialize方法，会设置$this->name
        $this->name = $name;
    }
    /**
     * 设置输入输出对象
     *
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return void
     */
    public function setIO(Input $input, Output $output): void
    {
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * 进程结束
     *
     * @return void
     */
    public function kill(): void
    {
        $this->killed = true;
    }
    /**
     * 进程任务执行主体，子类必须重写
     *
     * @return void
     */
    abstract public function process(): JsonTable;

    /**
     * 任务执行前的操作，主要用于每次任务执行前的初始化
     *
     * @param mix $data 本次处理的任务数据
     * @param array $info 本次任务信息
     * @return void
     */
    protected function loopInitialize(): void
    {
        // 获取默认驱动
        $handler = Cache::store();
        // 判定是否是RedisCluster驱动
        if ($handler instanceof RedisCluster) {
            // 连通性验证
            $num = 3;
            while ($num > 0) {
                $handler = Cache::store();
                if ($handler->ping()) {
                    break;
                }
                $this->echoMess('第' . (3 - $num + 1) . '次连接失败');
                // 连通性测试失败，则重新创建
                Cache::forgetDriver();
                $num--;
                \sleep(1);
            }
            if (0 === $num) {
                // 最终也未连通，则抛出异常
                Helper::exception('RedisCluster连接失败');
            }
        }
    }
    /**
     * 任务执行后的操作，主要用于本次任务执行后的清理
     *
     * @param JsonTable $jsonTable
     * @return void
     */
    protected function loopUninitialize(): void
    {
        $this->reset();
    }
    /**
     * 每次运行都会初始化一次
     *
     * @return void
     */
    protected function reset(): void
    {
        if (\time() - $this->lastTime > 300) {
            //强制重新创建新的数据库连接
            $this->app->db->connect(null, true);
            //清理掉Cache缓存
            $this->app->delete('cache');
            $this->lastTime = time();
        }
    }
}
