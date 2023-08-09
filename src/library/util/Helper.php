<?php

declare(strict_types=1);

namespace alocms\util;

use alocms\facade\JsonTable as JsonTableFacade;
use think\helper\Str;
use think\Model;

class Helper
{

    /**
     * md5盐值加密
     *
     * @param string $str 需要加密的字符串
     * @param string $salt 加密用的盐值
     * @param boolean $md5 需要加密的字符串是否已经经过一次md5加密
     * @return string 返回加密后的结果
     */
    public static function md5Salt(string $str, string $salt = 'cms', bool $md5 = false): string
    {
        return $md5 ? md5($str . $salt) : md5(md5($str) . $salt);
    }

    /**
     * 生成随机字符串
     *
     * @param integer $length 生成的随机字符串长度
     * @param integer $type 生成随机字符的类型,1小写字母，2大写字母，4数字，8特殊字符，可以组合
     * @return string 返回生成的随机字符串
     */
    public static function randStr(int $length = 16, int $type = 5): string
    {
        $chars = '';
        if (1 == (1 & $type)) {
            $chars .= 'abcdefghijklmnopqrstuvwxyz';
        }
        if (2 == (2 & $type)) {
            $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if (4 == (4 & $type)) {
            $chars .= '0123456789';
        }
        if (8 == (8 & $type)) {
            $chars .= '!@#$%^&*()_ []{}<>~`+=,.;:/?|';
        }
        $chars = \str_shuffle($chars);
        return \substr($chars, 0, $length);
    }

    /**
     * 获取今日0点时间戳
     *
     * @return integer
     */
    public static function zeroOfDay(int $time = null): int
    {
        $time = $time ?: time();
        return strtotime(date('Y-m-d', $time));
    }

    /**
     * 抛出异常
     *
     * @param string|JsonTable $msg 异常信息
     * @param integer|string $state 错误码
     * @param mix $data 扩展数据
     * @return void
     */
    public static function exception($msg = 'error', $state = 1, $data = null)
    {
        throw new CmsException($msg, $state, $data);
    }

    /**
     * 快捷创建模型
     *
     * @param string $name 模型名称，大小写敏感
     * @param bool $newInstance 是否创建新实例，默认false
     * @param string $layer 模型所在的层级
     * @return Model 返回实例化的模型对象
     */
    public static function model(string $name, bool $newInstance = false, string $layer = null): Model
    {
        $layer = $layer ?? 'common';
        $clazz = "\\app\\{$layer}\\model\\{$name}";
        if (!class_exists($clazz)) {
            $clazz = "\\app\\common\\model\\{$name}";
        }
        $instance = app($clazz, [], $newInstance);
        return $instance;
    }

    /**
     * JsonTable格式数据
     *
     * @param integer|string $state 状态码
     * @param mix $msg 消息内容
     * @param mix $data 扩展数据
     * @return array 返回JsonTable数据格式
     */
    public static function jtable($state, $msg, $data = null): array
    {
        $result = [
            'state' => $state,
            'msg' => $msg,
        ];
        return \is_null($data)
            ? $result
            : array_merge(
                $result,
                [
                    'data' => $data,
                ]
            );
    }

    /**
     * JsonTable成功数据
     *
     * @param mix $msg  消息内容
     * @param mix $data 扩展数据
     * @return array 返回JsonTable数据格式
     */
    public static function jsuccess($msg = 'success', $data = null): array
    {
        return static::jtable(0, $msg, $data);
    }

    /**
     * JsonTable错误数据
     *
     * @param string  $msg   消息内容
     * @param integer|string $state 状态码
     * @param mix $data  扩展数据
     * @return array 返回JsonTable数据格式
     */
    public static function jerror($msg = 'error', $state = 1, $data = null): array
    {
        return static::jtable($state, $msg, $data);
    }

    /**
     * 生成uuid
     *
     * @param string $splitChar 分割字符
     * @return string 返回uuid
     */
    public static function makeUUID(string $splitChar = ''): string
    {
        $chars = md5(uniqid((string)mt_rand(), true));
        return substr($chars, 0, 8) . $splitChar . substr($chars, 8, 4) . $splitChar . substr($chars, 12, 4) . $splitChar . substr(
            $chars,
            16,
            4
        ) . $splitChar . substr($chars, 20, 12);
    }

    /**
     * 删除字段前缀
     *
     * @param array $data 待处理的数据
     * @param string|null $prefix 前缀
     * @param array $exclude 排除的key值集合
     * @return array 返回处理后的数组
     */
    public static function delPrefixArr(array $data, ?string $prefix = null, array $exclude = []): array
    {
        if (\is_null($prefix)) {
            return $data;
        }
        // 去除前缀后的数据单独放到一个数组内
        $result = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, $exclude)) {
                // 找到匹配前缀且不在排除数组内的数据
                $result[static::delPrefix($key, $prefix)] = $value;
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * 获取删除前缀后的数据
     *
     * @param string $key 处理的数据
     * @param string $prefix 前缀
     * @return string
     */
    public static function delPrefix(string $key, ?string $prefix = null): string
    {
        if (\is_null($prefix)) {
            return $key;
        } elseif (static::existsPrefix($key, $prefix)) {
            return Str::substr($key, Str::length($prefix));
        } else {
            return $key;
        }
    }

    /**
     * 获取添加前缀后的数据
     *
     * @param string $key 处理的数据
     * @param string|null $prefix 前缀
     * @return string 返回处理后的key名
     */
    public static function addPrefix(string $key, ?string $prefix = null): string
    {
        if (\is_null($prefix)) {
            return $key;
        } elseif (static::existsPrefix($key, $prefix)) {
            return $key;
        } else {
            return "{$prefix}{$key}";
        }
    }

    /**
     * 获取添加前缀后的数据
     *
     * @param array $data 待处理的数据
     * @param string|null $prefix 前缀
     * @param array $exclude 排除的key值集合
     * @return array 返回处理后的数组
     */
    public static function addPrefixArr(array $data, ?string $prefix = null, array $exclude = []): array
    {
        if (\is_null($prefix)) {
            return $data;
        }
        // 去除前缀后的数据单独放到一个数组内
        $result = [];
        foreach ($data as $key => $value) {
            if (!\in_array($key, $exclude)) {
                // 非排除的key值，则添加前缀
                $result[static::addPrefix($key, $prefix, $exclude)] = $value;
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * 获取添加前缀后的数据(二维数组)
     *
     * @param array $data 待处理的数据
     * @param string|null $prefix 前缀
     * @param array $exclude 排除的key值集合
     * @return array 返回处理后的新数组
     */
    public static function addPrefixArrAll(array $data, ?string $prefix = null, array $exclude = []): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (\is_array($value)) {
                // 是数组，则递归调用
                $result[] = static::addPrefixArrAll($value, $prefix, $exclude);
            } else {
                if (!\in_array($key, $exclude)) {
                    // 非排除的key值，则添加前缀
                    $result[static::addPrefix($key, $prefix, $exclude)] = $value;
                } else {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * 获取删除前缀后的数据
     *
     * @param string $key 键名
     * @param string|null $prefix 旧前缀
     * @param string $newPre 新前缀
     * @param array $exclude 排除的key值集合
     * @return string 处理后的键名
     */
    public static function updatePrefix(
        string $key,
        ?string $prefix = null,
        string $newPre = ''
    ): string {
        if (\is_null($prefix)) {
            return $key;
        } elseif (static::existsPrefix($key, $prefix)) {
            return $newPre . Str::substr($key, Str::length($prefix));
        } else {
            return $key;
        }
    }

    /**
     * 获取删除前缀后的数据
     *
     * @param array $data 待处理数组
     * @param string|null $prefix 前缀
     * @param string $newPre 新前缀
     * @param array $exclude 排除的key集合
     * @return array 处理后的数组
     */
    public static function updatePrefixArr(
        array $data,
        ?string $prefix = null,
        string $newPre = '',
        array $exclude = []
    ): array {
        if (\is_null($prefix)) {
            return $data;
        }
        // 更新前缀后的数据单独放到一个数组内
        $result = [];
        foreach ($data as $key => $value) {
            if (!\in_array($key, $exclude)) {
                // 非排除的key值，则更新前缀
                $result[static::updatePrefix($key, $prefix, $newPre, $exclude)] = $value;
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * 判断指定键名是否带有指定前缀
     *
     * @param string $key 键名
     * @param string $prefix 前缀
     * @return boolean 返回判断前缀是否存在的结果
     */
    public static function existsPrefix(string $key, string $prefix = null): bool
    {
        return \is_null($prefix) ? false : Str::startsWith($key, $prefix);
    }

    /**
     * 删除数组内值为空的键
     *
     * @param array $data 待处理的数组,kv结构
     * @return array 返回处理后的数组
     */
    public static function arrayDelEmpty(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if ('' === $value) {
                $result[] = $value;
            }
        }
        return $result;
    }

    /**
     * 日志行为监听
     *
     * @param string $channel 通道
     * @param string $msg 摘要数据
     * @param mix $data 日志数据
     * @param string $level 级别，debug info warning error critical alert emergency
     *
     * @return mix 返回监听log事件的所有行为返回值集合
     */
    public static function logListen(string $channel, string $msg, $data = null, string $level = 'info'): void
    {
        /** @var \alocms\Request $request */
        $request = request();
        // 组装数据
        $eventData = [
            'channel' => $channel,
            'msg' => $msg,
            'level' => $level,
            'request_id' => $request->requestId(),
            'data' => \json_encode($data, JSON_UNESCAPED_UNICODE),
        ];
        // 如果是cli模式，在控制台输出一份
        if ($request->isCli()) {
            dump($eventData);
        }
        // 触发日志事件
        \event('Log', $eventData);
    }

    /**
     * 调试日志记录
     *
     * @param string $channel 通道
     * @param string $msg 摘要数据
     * @param mix $data 日志数据
     * @return void
     */
    public static function logListenDebug(string $channel, string $msg, $data = null): void
    {
        static::logListen($channel, $msg, $data, 'debug');
    }

    /**
     * 错误日志记录，用于记录业务执行失败的日志
     *
     * @param string $channel 通道
     * @param string $msg 消息
     * @param mix $data 日志数据
     * @return void
     */
    public static function logListenError(string $channel, string $msg, $data = null)
    {
        static::logListen($channel, $msg, $data, 'error');
    }

    /**
     * 警告日志记录，用于记录一些不影响业务的警告信息
     *
     * @param string $channel 通道
     * @param string $msg 消息
     * @param mix $data 日志数据
     * @return void
     */
    public static function logListenWarning(string $channel, string $msg, $data = null)
    {
        static::logListen($channel, $msg, $data, 'warning');
    }

    /**
     * 异常日志记录，用于异常捕获时
     *
     * @param string $channel 通道
     * @param string $msg 消息
     * @param mix $data 日志数据
     * @return void
     */
    public static function logListenCritical(string $channel, string $msg, $data = null)
    {
        static::logListen($channel, $msg, $data, 'critical');
    }
    /**
     * 记录异常日志
     *
     * @param string $class 异常产生所在类名
     * @param string $function 异常产生所在函数名
     * @param \Throwable $ex 异常对象
     * @return JsonTable 返回JsonTable对象
     */
    public static function logListenException(string $class, string $function, \Throwable $ex, array $data = []): JsonTable
    {
        $ex instanceof CmsException
            ? static::logListenError(
                $class,
                $function . ":{$ex->getMessage()}",
                [
                    'exception_data' => $ex->getData(),
                    'origin_data' => $data,
                ]
            )
            : static::logListenCritical(
                $class,
                $function . ":{$ex->getMessage()}",
                [
                    'trace' => $ex->getTrace(),
                    'origin_data' => $data,
                ]
            );
        return JsonTableFacade::error(
            $ex->getMessage(),
            $ex instanceof CmsException ? $ex->getState() : 1,
            $ex instanceof CmsException ? $ex->getData() : $ex->getTrace()
        );
    }

    /**
     * 判断内容并抛出异常
     *
     * @param JsonTable $jsonTable jsonTable对象
     * @return JsonTable 返回传递进来的JsonTable对象
     * @throws YzbException
     */
    public static function throwifJError(JsonTable $jsonTable): JsonTable
    {
        if (!$jsonTable->isSuccess()) {
            throw new CmsException($jsonTable);
        }
        return $jsonTable;
    }
    /**
     * 检测IP是否白名单
     *
     * @param string $ip 客户端ip
     * @param string|array $rules 白名单规则
     * @return boolean 返回校验结果，true表示通过，false表示不通过
     */
    public static function checkIP(string $ip, $rules): bool
    {
        if (!is_array($rules)) {
            $rules = [$rules];
        }
        foreach ($rules as $rule) {
            // 将.*临时替换成别的符号(.和*都是正则中有特殊含义的符号
            $rule_regexp = str_replace('.*', 'tmp', $rule);
            // 向规则字符串中增加转移，避免字符串中有其他特殊字符印象正则匹配
            // 非必要语句本例可以忽略
            $rule_regexp = preg_quote($rule_regexp, '/');
            // 将临时符号替换成正则表达式
            $rule_regexp = str_replace('tmp', '\.\d{1,3}', $rule_regexp);
            // 返回匹配结果
            $result = 1 == \preg_match('/^' . $rule_regexp . '$/', $ip);
            if (false === $result) {
                return false;
            }
        }
        return true;
    }
}
