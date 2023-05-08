<?php

declare(strict_types=1);

namespace {

    /**
     * md5盐值加密
     *
     * @param string $str 需要加密的字符串
     * @param string $salt 加密用的盐值
     * @param boolean $md5 需要加密的字符串是否已经经过一次md5加密
     * @return string 返回加密后的结果
     */
    function md5Salt(string $str, string $salt = 'cms', bool $md5 = false): string
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
    function jtable($state, $msg, $data = null): array
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
    function jsuccess($msg = 'success', $data = null): array
    {
        return jtable(0, $msg, $data);
    }

    /**
     * JsonTable错误数据
     *
     * @param string  $msg   消息内容
     * @param integer|string $state 状态码
     * @param mix $data  扩展数据
     * @return array 返回JsonTable数据格式
     */
    function jerror($msg = 'error', $state = 1, $data = null): array
    {
        return jtable($state, $msg, $data);
    }

    /**
     * 生成uuid
     *
     * @param string $splitChar 分割字符
     * @return string 返回uuid
     */
    function makeUUID(string $splitChar = ''): string
    {
        $chars = md5(uniqid((string)mt_rand(), true));
        return substr($chars, 0, 8) . $splitChar . substr($chars, 8, 4) . $splitChar . substr($chars, 12, 4) . $splitChar . substr(
            $chars,
            16,
            4
        ) . $splitChar . substr($chars, 20, 12);
    }
}
