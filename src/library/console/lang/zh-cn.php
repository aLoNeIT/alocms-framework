<?php
/**
 * 控制台语言文件
 * @author aLoNe.Adams.K <alone@alonetech.com>
 */
return [
    'command_not_support' => '不支持的命令[{:cmd}]',
    'class_not_found' => '未找到有效的类',
    'task_not_found' => '指定任务不存在',
    'task_execute_success' => '任务执行成功',
    'task_execute_error' => '任务执行失败[{:content}]',
    'task_no_data' => '暂无任务数据',
    'task_begin' => '任务开始执行',
    'task_end' => '任务执行结束',
    'task_error_function' => '无效的任务函数[{:function}]',
    'task_execute_limit' => '任务执行次数超出限制[{:num}]',
    'process_is_running' => '进程正在运行',
    'process_is_excepting' => '进程发生异常[{:content}]',
    'process_is_terminating' => '进程[{:pid}]正在终止',
    'process_terminate_timeout' => '进程[{:pid}]终止失败[超时]',
    'process_terminate_success' => '进程[{:pid}]终止成功',
    'process_terminate_fail' => '进程[{:pid}]终止失败[{:content}]',
    'process_signaled' => '进程[{:pid}]收到[{:signal}]信号',
    'worker_signaled' => '工作进程[{:workerId}]收到[{:signal}]信号',
    'worker_is_running' => '工作进程[{:workerId}]正在启动',
    'worker_is_stopping' => '工作进程[{:workerId}]正在停止',
    'worker_is_excepting' => '工作进程[{:workerId}]发生异常[{:content}]',
    'delete_file' => '正在删除文件[{:fileName}]',
    'function_not_callable' => '无法执行的函数',
    'ranking_list_error' => '排行榜[{:typeName}]处理失败[{:msg}]',
    'cron_class_not_found' => '定时任务类名[{:class}]未找到',
    'cron_task_running' => '定时任务[{:class}]正在执行',
    'cron_task_published' => '定时任务[{:class}]已发布',
    'cron_task_consumed' => '定时任务[{:class}]已消费',
    'consumer_execute_maximum' => '消费者已达到最大执行次数[{:maximum}]',
];
