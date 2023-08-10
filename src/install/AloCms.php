<?php

declare(strict_types=1);

namespace alocms\install;

use alocms\facade\JsonTable as JsonTableFacade;
use alocms\util\Helper;
use alocms\util\JsonTable;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Config;
use think\facade\Db;
use think\response\Json;

/**
 * 项目操作
 */
class AloCms extends Command
{
    /**
     * JsonTable对象
     *
     * @var \alocms\util\JsonTable
     */
    protected $jsonTable = null;
    /**
     * aLoCMS对象
     *
     * @var \alocms\AloCms
     */
    protected $alocms = null;

    /** @inheritDoc */
    protected function configure(): void
    {
        $this->setName('alocms')->setDescription('aLoCMS项目管理')->setHelp('help')
            ->addArgument('cmd', Argument::REQUIRED, '命令');
    }

    /** @inheritDoc */
    protected function initialize(Input $input, Output $output): void
    {
        parent::initialize($input, $output);
        $this->jsonTable = $this->app->make('JsonTable', [], true);
        $this->alocms = $this->app->alocms;
    }
    /**
     * 命令行执行主体函数
     *
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return integer 返回执行结果，0成功，非0失败
     */
    protected function execute(Input $input, Output $output): int
    {
        //获取执行指令
        $command = $input->getArgument('cmd');
        //默认执行成功
        $jResult = JsonTableFacade::success();
        //根据指令决定执行方法
        switch ($command) {
            case 'install':
                $jResult = $this->install($input, $output);
                break;
            case 'uninstall':
                $jResult = $this->uninstall($input, $output);
                break;
            default:
                $jResult = $this->jsonTable->error("暂不支持该命令[{$command}]");
                break;
        }
        if (!$jResult->isSuccess()) {
            Helper::logListenError(static::class, $jResult->msg);
        } else {
            Helper::logListen(static::class, '执行成功');
        }
        return $jResult->state;
    }
    /**
     * 安装项目
     *
     * @return JsonTable
     */
    protected function install(): JsonTable
    {
        try {
            $lockFile = $this->alocms->getRootPath('install') . 'install.lock';
            if (\file_exists($lockFile)) {
                return $this->jsonTable->error('系统已初始化');
            }
            // 获取是否支持json字段的配置
            $disableJson = Config::get('alocms.install.disable_json', false);
            // 获取sql所在目录
            $sqlPath = $this->alocms->getRootPath('install/sql');
            // 获取所有sql文件
            $files = glob("{$sqlPath}/*.sql");
            // 获取表前缀
            $prefix = Db::connect()->getConfig('prefix');
            // 循环处理执行sql
            Db::startTrans();
            try {
                foreach ($files as $file) {
                    Helper::logListen(static::class, "正在处理文件[{$file}]");
                    // 读取内容
                    $content = file_get_contents($file);
                    $content = str_replace("\r", "\n", $content);
                    $content = str_replace('{$database_prefix}_', $prefix, $content);
                    // 分割SQL语句
                    $sqls = explode(";\n", $content);
                    foreach ($sqls as $sql) {
                        $sql = trim($sql);
                        if (empty($sql)) continue;
                        // 判断是否支持json字段
                        if (false !== $disableJson) {
                            $sql = \is_string($disableJson)
                                ? \str_replace(' json ', " {$disableJson} ", $sql)
                                : \str_replace(' json ', " varchar(max) ", $sql);
                        }
                        Db::execute("{$sql}");
                    }
                }
                Db::commit();
            } catch (\Throwable $ex) {
                Db::rollback();
                return Helper::logListenException(static::class, __FUNCTION__, $ex);
            }
            // 生成lock文件
            \file_put_contents($lockFile, date('Y-m-d H:i:s'));
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }
    /**
     * 卸载项目
     *
     * @return JsonTable
     */
    protected function uninstall(): JsonTable
    {
        try {
            // 获取sql所在目录
            $sqlPath = $this->alocms->getRootPath('install/sql');
            // 获取所有sql文件
            $files = glob("{$sqlPath}/*.sql");
            // 获取表前缀
            $prefix = Db::connect()->getConfig('prefix');
            Db::startTrans();
            try {
                foreach ($files as $file) {
                    Helper::logListen(static::class, "正在处理文件[{$file}]");
                    // 读取内容
                    $content = file_get_contents($file);
                    $content = str_replace("\r", "\n", $content);
                    $content = str_replace('{$database_prefix}_', $prefix, $content);
                    // 分割SQL语句
                    $sqls = explode(";\n", $content);
                    foreach ($sqls as $sql) {
                        $sql = trim($sql);
                        if (empty($sql) || strpos(strtolower($sql), 'drop table') === false) continue;
                        Db::execute("{$sql};");
                    }
                }
                Db::commit();
            } catch (\Throwable $ex) {
                Db::rollback();
                return Helper::logListenException(static::class, __FUNCTION__, $ex);
            }
            $lockFile = $this->alocms->getRootPath('install') . 'install.lock';
            file_exists($lockFile) && unlink($lockFile);
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }
}
