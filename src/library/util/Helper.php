<?php

declare(strict_types=1);

namespace alocms\library\util;

use think\helper\Str;

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
    function arrayDelEmpty(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if ('' === $value) {
                $result[] = $value;
            }
        }
        return $result;
    }
}
