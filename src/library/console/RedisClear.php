<?php

namespace alocms\library\console;

use alocms\library\console\Base as BaseConsole;
use alocms\library\facade\JsonTable;
use alocms\library\util\Helper;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Cache as CacheFacade;

/**
 * 使用SwoolePool特性来管理维护子类
 */
class RedisClear extends BaseConsole
{

    /**
     * 内部用到的key
     *
     * @var string
     */
    protected $key = 'process_swoole';

    protected const TYPE_REDIS_UNKNOW = 0;
    protected const TYPE_REDIS_STRING = 1;
    protected const TYPE_REDIS_SET = 2;
    protected const TYPE_REDIS_LIST = 3;
    protected const TYPE_REDIS_ZSET = 4;
    protected const TYPE_REDIS_HASH = 5;


    protected const TYPE_FUNCTION = [
        ['name' => 'none', 'delete' => 'delete', 'find' => 'get', 'count' => 'strlen', 'code' => 0],
        ['name' => 'string', 'delete' => 'delete', 'find' => 'get', 'count' => 'strlen', 'code' => 1],
        ['name' => 'set', 'delete' => 'sRem', 'find' => '', 'count' => 'sCard', 'code' => 2],
        ['name' => 'list', 'delete' => 'lRem', 'find' => 'get', 'count' => 'lLen', 'code' => 3],
        ['name' => 'zset', 'delete' => 'zRem', 'find' => '', 'count' => 'zCard', 'code' => 4],
        ['name' => 'hash', 'delete' => 'hDel', 'find' => 'hGet', 'count' => 'hLen', 'code' => 5]
    ];

    protected function configure()
    {
        $this->setName('redis:clear')
            ->addArgument('cmd', null, Argument::REQUIRED, 'this is console command')
            ->addOption('type', 0, Option::VALUE_REQUIRED, 'key type')
            ->addOption('name', null, Option::VALUE_REQUIRED, 'key name')
            ->addOption('value', 0, Option::VALUE_REQUIRED, 'deal value');
    }

    /**
     * 命令行执行主体函数
     *
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return void
     */
    protected function execute(Input $input, Output $output)
    {
        //        获取执行指令
        $command = $input->getArgument('cmd');
        //        默认执行成功
        $jResult = JsonTable::success();
        //根据指令决定执行方法
        switch ($command) {
            case 'keys':
                $jResult = $this->keys($input, $output);
                break;
            case 'delete':
                $jResult = $this->delete($input, $output);
                break;
            case 'find':
                $jResult = $this->find($input, $output);
                break;
            case 'count':
                $jResult = $this->count($input, $output);
                break;
            case 'dict':
                $jResult = $this->dict($input, $output);
                break;
            default:
                $jResult = $jResult->error(lang('find', [
                    'cmd' => $command,
                ]));
                break;
        }
        if (!$jResult->isSuccess()) {
            $this->echoMess(lang('redis_exec_error', [
                'content' => $jResult->msg,
            ]));
        }
        $output->writeln($jResult);
        return $jResult->state;
    }

    /**
     * 格式化输出内容
     *
     * @param string $msg 消息体
     * @param integer $state 状态码
     * @param array $table 扩展数据
     * @return void
     */
    protected function echoMess($msg, $state = 0, $table = [])
    {
        $result = [
            'time' => date('Y-m-d H:i:s'),
            'msg' => $msg,
            'state' => $state,
        ];
        if (!empty($table)) {
            $result['table'] = $table;
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE), PHP_EOL;
    }

    /**
     *  keys  查询所有key
     *
     * @param Input  $input
     * @param Output $output
     *
     * @return mixed
     *
     * User: Loong
     * Date: 2021/7/21
     * Time: 18:18
     */
    protected function keys(Input $input, Output $output)
    {
        $jResult = JsonTable::success();
        try {
            if ($input->hasOption('name')) {
                $name =  $input->getOption('name');
            } else {
                $name = '*';
            }
            $key = CacheFacade::store('redis')->keys($name);
            $jResult->success('success', $key);
        } catch (\Exception $ex) {
            $this->echoMess(lang('redis_keys', [
                'content' => $ex->getMessage(),
            ]), 1);
            $jResult->error($ex->getMessage());
            Helper::logListenCritical(static::class, __FUNCTION__ . ":{$ex->getMessage()}", $ex instanceof \Exception ? $ex->getTrace() : $ex->getData());
        }
        return $jResult;
    }

    /**
     *  delete  删除指定key
     *
     * @param Input  $input
     * @param Output $output
     *
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * User: Loong
     * Date: 2021/7/21
     * Time: 18:19
     */
    protected function delete(Input $input, Output $output)
    {
        $jResult = JsonTable::success();
        $result = false;
        try {
            if ($input->hasOption('name')) {
                $name =  $input->getOption('name');
            } else {
                return $jResult->error('param name lost');
            }
            $redis = CacheFacade::store('redis');
            $isExist = $redis->has($name);
            if ($isExist === true) {
                $type = self::TYPE_REDIS_UNKNOW;
                if ($input->hasOption('type')) {
                    $type =  $input->getOption('type');
                }
                $value = '';
                if ($input->hasOption('value')) {
                    $value =  $input->getOption('value');
                }
                if ($type ===  self::TYPE_REDIS_UNKNOW || $value === '') {
                    $result = $redis->delete($name);
                } else {
                    $methodname = self::TYPE_FUNCTION[$type][__FUNCTION__] ?? 'delete';
                    $result = call_user_func(array($redis, $methodname), $name, $value);
                }
            }
            $jResult->success('success', $result);
        } catch (\Exception $ex) {
            $this->echoMess(lang('redis_keys', [
                'content' => $ex->getMessage(),
            ]), 1);
            $jResult->error($ex->getMessage());
            Helper::logListenCritical(static::class, __FUNCTION__ . ":{$ex->getMessage()}", $ex instanceof \Exception ? $ex->getTrace() : $ex->getData());
        }
        return $jResult;
    }

    /**
     *  find  查看指定key
     *
     * @param Input  $input
     * @param Output $output
     *
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * User: Loong
     * Date: 2021/7/21
     * Time: 18:19
     */
    protected function find(Input $input, Output $output)
    {
        $jResult = JsonTable::success();
        $result = false;
        try {
            if ($input->hasOption('name')) {
                $name =  $input->getOption('name');
            } else {
                return $jResult->error('param name lost');
            }
            $redis = CacheFacade::store('redis');
            $isExist = $redis->has($name);
            if ($isExist === true) {
                $type = self::TYPE_REDIS_UNKNOW;
                if ($input->hasOption('type')) {
                    $type =  $input->getOption('type');
                }
                if ($type === self::TYPE_REDIS_UNKNOW || $type === self::TYPE_REDIS_STRING) {
                    $result = $redis->get($name);
                } else {
                    $value = '';
                    if ($input->hasOption('value')) {
                        $value =  $input->getOption('value');
                    }
                    if ($type ===  self::TYPE_REDIS_HASH && $value !== '') {
                        $result = $redis->hGet($name, $value);
                    } else {
                        $result = $jResult->error('can not find');
                    }
                }
            }
            $jResult->success('success', $result);
        } catch (\Exception $ex) {
            $this->echoMess(lang('redis_keys', [
                'content' => $ex->getMessage(),
            ]), 1);
            $jResult->error($ex->getMessage());
            Helper::logListenCritical(static::class, __FUNCTION__ . ":{$ex->getMessage()}", $ex instanceof \Exception ? $ex->getTrace() : $ex->getData());
        }
        return $jResult;
    }

    /**
     *  count  统计key
     *
     * @param Input  $input
     * @param Output $output
     *
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * User: Loong
     * Date: 2021/7/21
     * Time: 18:20
     */
    protected function count(Input $input, Output $output)
    {
        $jResult = JsonTable::success();
        $result = false;
        try {
            if ($input->hasOption('name')) {
                $name =  $input->getOption('name');
            } else {
                return $jResult->error('param name lost');
            }
            $redis = CacheFacade::store('redis');
            $isExist = $redis->has($name);
            if ($isExist === true) {
                $type = self::TYPE_REDIS_UNKNOW;
                if ($input->hasOption('type')) {
                    $type =  $input->getOption('type');
                }
                $methodname = self::TYPE_FUNCTION[$type][__FUNCTION__] ?? 'strlen';
                $result = call_user_func(array($redis, $methodname), $name);
            }
            $jResult->success('success', $result);
        } catch (\Exception $ex) {
            $this->echoMess(lang('redis_keys', [
                'content' => $ex->getMessage(),
            ]), 1);
            $jResult->error($ex->getMessage());
            Helper::logListenCritical(static::class, __FUNCTION__ . ":{$ex->getMessage()}", $ex instanceof \Exception ? $ex->getTrace() : $ex->getData());
        }
        return $jResult;
    }


    protected function dict(Input $input, Output $output)
    {
        //pre:me:dict:1002:3
        $jResult = JsonTable::success();
        $result = false;
        try {
            if ($input->hasOption('name')) {
                $name =  $input->getOption('name');
            } else {
                return $jResult->error('param name lost');
            }
            $value =  $input->getOption('value') ?? 0;

            $dictName = 'dict:' . $name . ':' . $value;
            $redis = CacheFacade::store('redis');
            $isExist = $redis->has($dictName);
            if ($isExist === true) {
                $result = $redis->delete($dictName);
            }
            $jResult->success('success:' . $dictName, $result);
        } catch (\Exception $ex) {
            $this->echoMess(lang('redis_keys', [
                'content' => $ex->getMessage(),
            ]), 1);
            $jResult->error($ex->getMessage());
            Helper::logListenCritical(static::class, __FUNCTION__ . ":{$ex->getMessage()}", $ex instanceof \Exception ? $ex->getTrace() : $ex->getData());
        }
        return $jResult;
    }
}
