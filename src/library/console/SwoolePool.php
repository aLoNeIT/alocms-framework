<?php

declare(strict_types=1);

namespace alocms\console;

use alocms\console\process\Base as BaseProcess;
use alocms\facade\JsonTable as JsonTableFacade;
use alocms\util\Helper;
use alocms\util\JsonTable;
use Swoole\Process;
use Swoole\Table;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

/**
 * 方便IDE提示
 * @property \Swoole\Table $table 存储共享数据表
 */
class AloCmsPool extends \Swoole\Process\Pool
{
}

/**
 * 使用SwoolePool特性来管理维护子类
 */
class SwoolePool extends Base
{

    /**
     * 内部用到的key
     *
     * @var string
     */
    protected $key = 'process_swoole';
    /**
     * 临时目录路径
     *
     * @var string
     */
    protected $runtimePath = '';
    /**
     * 当前进程状态
     *
     * @var string
     */
    protected $status = '';
    /**
     * 生成的pid文件路径
     *
     * @var string
     */
    protected $pidFile = '';
    /**
     * 配置信息
     *
     * @var array
     */
    protected $config = [
        'temp_path' => '', //运行期临时目录
        'size' => 32, //该值不能小于task总数，且必须为2的倍数
        'timeout' => 60,
        'sleep_time' => 30,
        'sleep_step' => 50,
        'task' => [
            // 测试代码
            [
                'name' => 'ProcessTest',
                'class' => 'alocms\console\process\ProcessTest',
                'worker_num' => 1,
                'loop_num' => 1000,
                'sleep_time' => 1,
                'sleep_step' => 1,
                'mutex' => false,
            ],
            // 定时任务发布者
            [
                'name' => 'CronPublisher',
                'class' => 'alocms\console\process\CronPublisher',
                'worker_num' => 1,
                'loop_num' => 1000,
                'sleep_time' => 1,
                'sleep_step' => 1,
                'mutex' => false,
            ],
            // 定时任务消费者
            [
                'name' => 'CronConsumer',
                'class' => 'alocms\console\process\CronConsumer',
                'worker_num' => 1,
                'loop_num' => 1000,
                'sleep_time' => 1,
                'sleep_step' => 1,
                'mutex' => false,
            ],
        ]
    ];

    /** @inheritDoc */
    protected function configure(): void
    {
        $this->setName('swoole:pool')->setDescription('Create Swoole background processpool for task.')->setHelp('help')
            ->addArgument('cmd', Argument::REQUIRED, 'this is console command')
            ->addOption('name', null, Option::VALUE_REQUIRED, 'process name')
            ->addOption('num', null, Option::VALUE_REQUIRED, 'process excute num', 1)
            ->addOption('signum', null, Option::VALUE_REQUIRED, 'process signal signum', 15)
            ->addOption('extra', null, Option::VALUE_REQUIRED, 'extra params');
    }
    /** @inheritDoc */
    protected function execute(Input $input, Output $output): int
    {
        //获取执行指令
        $command = $input->getArgument('cmd');
        //默认执行成功
        $jResult = JsonTableFacade::success();
        //根据指令决定执行方法
        switch ($command) {
            case 'start':
                $jResult = $this->start($input, $output);
                break;
            case 'stop':
                $jResult = $this->stop($input, $output);
                break;
            case 'restart':
                $jResult = $this->restart($input, $output);
                break;
            case 'run':
                $jResult = $this->runOnce($input, $output);
                break;
            case 'signal':
                $jResult = $this->signal($input, $output);
                break;
            default:
                $jResult = JsonTableFacade::error(lang('command_not_support', [
                    'cmd' => $command,
                ]));
                break;
        }
        if (!$jResult->isSuccess()) {
            $this->echoMess(lang('task_execute_error', [
                'content' => $jResult->msg,
            ]));
        }
        return $jResult->state;
    }
    /** @inheritDoc */
    protected function initialize(Input $input, Output $output)
    {
        parent::initialize($input, $output);
        $config = \config('swoole_pool', []);
        //合并配置项
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
        $serverName = \config('system.server_name', 'alocms');
        if (!is_null($serverName)) {
            $this->key = "{$serverName}_{$this->key}";
        }
        $this->runtimePath = $this->config['temp_path'] ?? runtime_path();
        $this->pidFile = "{$this->runtimePath}{$this->key}.pid";
    }
    /**
     * 对指定任务发送信号
     *
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return JsonTable 返回执行结果
     */
    protected function signal(Input $input, Output $output): JsonTable
    {
        $jResult = JsonTableFacade::success();
        $name = $input->getOption('name');
        $signum = $input->getOption('signum');
        $process = new Process(function (Process $childProcess) use ($name, $signum) {
            $rootPath = root_path();
            $sh = "{$rootPath}sh" . DIRECTORY_SEPARATOR . 'signal.sh';
            $childProcess->exec($sh, [$name, $signum]);
        });
        $process->start();
        Process::wait();
        return $jResult;
    }

    /**
     * 执行指定脚本任务
     *
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return JsonTable 返回执行结果
     */
    protected function runOnce(Input $input, Output $output): JsonTable
    {
        $jResult = JsonTableFacade::error(lang('task_not_found'));
        try {
            $config = $this->config['task'];
            $name = $input->getOption('name');
            $num = $input->getOption('num');
            $taskExist = false;
            //循环exec下内容，匹配name值
            foreach ($config as $item) {
                $taskExist = $name == $item['name'];
                if ($taskExist) {
                    $classNames = $item['class'];
                    if (is_string($classNames)) {
                        $classNames = [$classNames];
                    }

                    foreach ($classNames as $className) {
                        //判断类是否存在，若存在则实例化并执行
                        if (!class_exists($className)) {
                            $this->echoMess(lang('class_not_found'));
                            continue;
                        }
                        /** @var \alocms\console\process\Api $processObj */
                        $processObj = new $className(0, null, $item['name']);
                        $processObj->setIO($input, $output);
                        $processObj->mutex = \boolval($item['mutex'] ?? false);
                        while ($num-- > 0) {
                            $jResult = $processObj->process();
                            if (!$jResult->isSuccess()) {
                                break;
                            }
                        }
                    }
                    break;
                }
            }
            //如果未找到，则将其当做类名来处理
            if (!$taskExist) {
                if (class_exists($name)) {
                    $processObj = new $name(0, null, "自定义类[{$name}]");
                    $processObj->setIO($input, $output);
                    while ($num-- > 0) {
                        $jResult = $processObj->process();
                        if (!$jResult->isSuccess()) {
                            break;
                        }
                    }
                }
            }
        } catch (\Throwable $ex) {
            $this->echoMess(lang('process_is_excepting', [
                'content' => $ex->getMessage(),
            ]), 1);
            $jResult = Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
        return $jResult;
    }
    /**
     * 重启进程
     *
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return JsonTable 返回执行结果
     */
    protected function restart(Input $input, Output $output): JsonTable
    {
        $jResult = $this->stop($input, $output);
        if ($jResult->isSuccess()) {
            $jResult = $this->start($input, $output);
        }
        return $jResult;
    }
    /**
     * 停止进程
     *
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return JsonTable 返回执行结果
     */
    protected function stop(Input $input, Output $output): JsonTable
    {
        $jResult = JsonTableFacade::success();
        try {
            $pid = 0;
            //检查pid文件是否存在
            if (file_exists($this->pidFile)) {
                //读取文件内pid，并发送关闭信号
                $pid = file_get_contents($this->pidFile);
                $i = 0;
                //循环检测关闭
                while ($i++ < intval($this->config['timeout']) && Process::kill($pid, 0)) {
                    $this->echoMess(lang('process_is_terminating', [
                        'pid' => $pid,
                    ]));
                    Process::kill($pid);
                    \sleep(1);
                }
                //退出循环后再次检测
                if (!Process::kill($pid, 0)) {
                    $this->echoMess(lang('process_terminate_success', [
                        'pid' => $pid,
                    ]));
                    //退出成功删除文件
                    @file_exists($this->pidFile) && @unlink($this->pidFile);
                } else {
                    $this->echoMess(lang('process_terminate_timeout', [
                        'pid' => $pid,
                    ]), 1);
                }
            } else {
                $this->echoMess(lang('process_terminate_fail', [
                    'pid' => $pid,
                    'content' => 'pid文件不存在',
                ]), 1);
            }
        } catch (\Throwable $ex) {
            $this->echoMess(lang('process_is_excepting', [
                'content' => $ex->getMessage(),
            ]), 1);
            $jResult = Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
        return $jResult;
    }
    /**
     * 启动进程
     *
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return JsonTable 返回执行成功与否
     */
    protected function start(Input $input, Output $output): JsonTable
    {
        $jResult = JsonTableFacade::success();
        try {
            //本地写入，判断进程是否已启动
            $pidFile = "{$this->runtimePath}{$this->key}.pid";
            if (file_exists($pidFile)) {
                return $jResult->error(lang('process_is_running'));
            }
            $pid = getmypid();
            //pid写入文件，用于下次进入校验
            file_put_contents($pidFile, $pid);
            $config = $this->config['task'];
            $size = $this->config['size'];
            $totalCount = 0;
            //创建swooleTable用于进程间共享数据
            //主要用于OnWorkerStop时候标记自身运行状态
            $swooleTable = new \Swoole\Table($size);
            $swooleTable->column('name', Table::TYPE_STRING, 50);
            //因为class现在设置为可以配置多个，所以将长度设置为1024
            //并且存的是json_encode后的数据
            $swooleTable->column('class', Table::TYPE_STRING, 1024);
            $swooleTable->column('loop_num', Table::TYPE_INT);
            $swooleTable->column('sleep_time', TABLE::TYPE_INT);
            $swooleTable->column('sleep_step', TABLE::TYPE_INT);
            $swooleTable->column('state', Table::TYPE_INT);
            $swooleTable->column('pid', Table::TYPE_INT);
            $swooleTable->create();
            //遍历所有运行任务
            foreach ($config as $value) {
                for ($i = 0; $i < $value['worker_num']; $i++) {
                    $swooleTable->set((string)$totalCount++, [
                        'name' => $value['name'],
                        'class' => json_encode($value['class']),
                        'loop_num' => intval($value['loop_num']),
                        'sleep_time' => intval($value['sleep_time'] ?? ($this->config['sleep_time'] ?? 0)),
                        'sleep_step' => intval($value['sleep_step'] ?? ($this->config['sleep_step'] ?? 0)),
                        'mutex' => \boolval($value['mutex'] ?? false),
                        'state' => 0,
                        'pid' => 0,
                    ]);
                }
            }
            $projectName = config('system.project_name', 'alocms');
            swoole_set_process_name("{$projectName}_swoole_master_{$pid}");
            //创建进程池对象
            /** @var AloCmsPool $pool */
            $pool = new \Swoole\Process\Pool($totalCount);
            $pool->table = $swooleTable;
            $pool->on("WorkerStart", function ($pool, $workerId) use ($input, $output, $projectName) {
                try {
                    $this->echoMess(lang('worker_is_running', [
                        'workerId' => $workerId,
                    ]));
                    $running = true;
                    $childPid = \getmypid();
                    $pool->table->set($workerId, [
                        'state' => 1,
                        'pid' => $childPid,
                    ]);
                    $config = $pool->table->get($workerId);
                    swoole_set_process_name("{$projectName}_swoole_{$config['name']}_{$workerId}_{$childPid}");
                    $classNames = json_decode($config['class']);
                    if (is_string($classNames)) {
                        $classNames = [$classNames];
                    }

                    //进程对象数组
                    $processObjs = [];
                    foreach ($classNames as $className) {
                        if (!\class_exists($className)) {
                            break;
                        }

                        //实例化类并判断是否继承自ProcessBase
                        //若继承，则加入进程对象数组
                        $this->echoMess($className);
                        $processObj = new $className($workerId, $pool, $config['name']);
                        $processObj->setIO($input, $output);
                        if ($config['sleep_time'] > 0) {
                            $processObj->sleepTime = $config['sleep_time'];
                        }
                        if ($config['sleep_step'] > 0) {
                            $processObj->sleepStep = $config['sleep_step'];
                        }
                        // 读取mutex值
                        $processObj->mutex = \boolval($config['mutex'] ?? false);
                        if ($processObj instanceof BaseProcess) {
                            $processObjs[] = $processObj;
                        }
                    }
                    //监听信号量
                    pcntl_signal(SIGTERM, function () use (&$running, $processObjs, $workerId, $pool) {
                        $running = false;
                        $pid = $pool->table->get($workerId)['pid'];
                        foreach ($processObjs as $processObj) {
                            //结束进程，输出日志
                            $this->echoMess(lang('worker_signaled', [
                                'workerId' => $workerId,
                                'signal' => 'SIGTERM',
                                'pid' => $pid,
                            ]));
                            $processObj->kill();
                        }
                    });
                    //执行对象内的函数
                    if (!empty($processObjs)) {
                        $loopNum = 0;
                        //开始运行中，且（循环次数为0或者小于执行循环次数，可以继续运行）
                        while ($running && (0 == $config['loop_num'] || $loopNum++ < $config['loop_num'])) {
                            foreach ($processObjs as $processObj) {
                                pcntl_signal_dispatch();
                                $processObj->process();
                            }
                        }
                    }
                } catch (\Throwable $ex) {
                    $this->echoMess(lang('worker_is_excepting', [
                        'workerId' => $workerId,
                        'content' => $ex->getMessage(),
                    ]), 1);
                    Helper::logListenCritical(static::class, "WorkerStart:{$ex->getMessage()}", $ex->getTrace());
                } finally {
                    //释放资源
                    //实际上不处理也可以，因为执行完毕后就会自动清理进程内所有占用资源
                    for ($i = 0; $i < count($processObjs); $i++) {
                        unset($processObjs[$i]);
                    }
                    $processObjs = null;
                }
            });

            $pool->on("WorkerStop", function ($pool, $workerId) {
                try {
                    $this->echoMess(lang('worker_is_stopping', [
                        'workerId' => $workerId,
                    ]));
                    //更新当前子进程运行状态为0
                    $pool->table->set($workerId, [
                        'state' => 0,
                    ]);
                } catch (\Throwable $ex) {
                    $this->echoMess(lang('worker_is_excepting', [
                        'workerId' => $workerId,
                        'content' => $ex->getMessage(),
                    ]), 1);
                    Helper::logListenCritical(static::class, "WorkerStop:{$ex->getMessage()}", $ex->getTrace());
                }
            });
            //阻塞执行
            $pool->start();
            //进程结束删除文件
            if (file_exists($this->pidFile)) {
                @unlink($this->pidFile);
            }
            $this->echoMess(lang('delete_file', [
                'fileName' => $this->pidFile,
            ]));
            return $jResult;
        } catch (\Throwable $ex) {
            if (file_exists($this->pidFile)) {
                @unlink($this->pidFile);
            }
            $this->echoMess(lang('process_is_excepting', [
                'content' => $ex->getMessage(),
            ]), 1);
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }
    /**
     * 格式化输出内容
     *
     * @param mixed $msg 消息体
     * @param string|integer $state 状态码
     * @param mixed $table 扩展数据
     * @return void
     */
    protected function echoMess($msg, $state = 0, $table = []): void
    {
        $result = [
            'time' => date('Y-m-d H:i:s'),
            'msg' => $msg,
            'state' => $state,
        ];
        if (!empty($table)) {
            $result['table'] = $table;
        }
        // 向终端输出内容
        echo json_encode($result, JSON_UNESCAPED_UNICODE), PHP_EOL;
    }
}
