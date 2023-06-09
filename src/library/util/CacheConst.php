<?php

declare(strict_types=1);

namespace alocms\util;

/**
 * 缓存常量
 */
final class CacheConst
{
    /**
     * 一分钟
     */
    const ONE_MINUTE = 60;
    /**
     * 三分钟
     */
    const THREE_MINUTE = 180;
    /**
     * 五分钟
     */
    const FIVE_MINUTE = 300;

    /**
     * 十分钟
     */
    const TEN_MINUTE = 600;
    /**
     * 十五分钟
     */
    const NINE_MINUTE = 900;
    /**
     * 一小时
     */
    const ONE_HOUR = 3600;
    /**
     * 半小时
     */
    const HALF_HOUR = 1800;
    /**
     * 两小时
     */
    const TWO_HOUR = 7200;
    /**
     * 一天
     */
    const ONE_DAY = 86400;

    /**
     * 五天
     */
    const FIVE_DAY = 432000;

    /**
     * 字典key前缀
     */
    const DICTIONARY = 'dictionary';
    /**
     * 手机号等待发送
     */
    const SMS_PHONE_WAITING = 'sms:phone:waiting';
    /**
     * 字典前缀
     */
    const DICT = 'dict';
    /**
     * 字典定义数据
     */
    const DICT_DEFINE = 'dict:define';
    /**
     * 进程互斥任务
     */
    const PROCESS_MUTEXT_TASK = 'process:mutex:task';
    /**
     * 系统任务记录
     */
    const TASK_RECORD = 'task_record';
    /**
     * 日志清洁工锁
     */
    const LOG_CLEANER_LOCK = 'cleaner:lock';

    /**
     * 登陆时间
     */
    const ACCOUNT_LOGIN = 'account:login';

    /**
     * 字典缓存key
     *
     * @param integer $id 字典id
     * @return string 返回字典缓存最终key
     */
    public static function dictionary(int $id): string
    {
        return self::DICTIONARY . ":{$id}";
    }

    /**
     * 手机号防止重复发短信验证key
     *
     * @param string $mp 手机号
     * @return string 返回key
     */
    public static function smsPhoneWaiting(string $mp): string
    {
        return self::SMS_PHONE_WAITING . ":{$mp}";
    }
    /**
     * 获取存储字典的缓存键名
     *
     * @param integer $id 字典id
     * @param integer $appType 应用类型
     * @return string
     */
    public static function dict(int $id, int $appType = 1): string
    {
        return self::DICT . ":{$id}:{$appType}";
    }
    /**
     * 获取存储字典定义的键名
     *
     * @param integer $id 字典id
     * @param integer $appType 应用类型
     * @return string 返回key
     */
    public static function dictDefine(int $id, int $appType = 1): string
    {
        return self::DICT_DEFINE . ":{$id}:{$appType}";
    }
    /**
     * 进程互斥任务名称
     *
     * @param string $name 任务名
     * @return string 返回key
     */
    public static function processMutexTask(string $name): string
    {
        return self::PROCESS_MUTEXT_TASK . ":{$name}";
    }

    /**
     * 系统任务记录锁名称
     *
     * @param string $name 任务名
     * @param integer $type 任务类型
     * @return string 返回生成的锁名称
     */
    public static function taskRecordLock(string $name, int $type): string
    {
        return self::TASK_RECORD . ":lock:{$name}:{$type}";
    }

    /**
     * 登录次数
     *
     * @param integer $id 字典id
     * @return string 返回会话登录次数名称
     */
    public static function accountLoginTimes(string $account, int $appType = 1): string
    {
        return self::ACCOUNT_LOGIN . ":times:{$account}:{$appType}";
    }
}
