<?php

namespace alocms\library\util;

use think\facade\Config;

/**
 *  错误码处理类
 */
class ErrCode
{
    /**
     * JsonTable对象
     *
     * @var JsonTable
     */
    protected $jsonTable = null;
    /**
     * 错误码
     *
     * @var array
     */
    protected $errorCode = [];
    /**
     * 构造函数
     */
    public function __construct()
    {
        //重新实例化
        $this->jsonTable = app('JsonTable', [], true);
        //载入错误码配置文件
        $this->errorCode = Config::get('errcode');
    }
    /**
     * 获取错误信息
     *
     * @param string|integer $state 错误码
     * @param array $param 错误信息参数，部分错误支持
     * @return array 返回错误信息数组
     */
    public function getError($state, $param = []): array
    {
        return $this->getJError($state, $param, false)->toArray();
    }

    /**
     * 获取JsonTable对象的错误
     *
     * @param $state 错误状态码
     * @param array $param 额外信息参数
     * @return JsonTable 返回包含错误信息的JsonTable对象
     */
    public function getJError($state, $param = [], $clone = true)
    {
        $state = strval($state);
        $msg = isset($this->errorCode[$state]) ? lang($this->errorCode[$state], $param) : '';
        return $clone ? $this->jsonTable->withMessage($msg, $state) : $this->jsonTable->message($msg, $state);
    }

    /**
     *  getJErrorWithData   获取JsonTable对象的错误 包含data
     *
     * @param       $state  错误状态码
     * @param array $param  额外信息参数
     * @param array $data  错误码
     * @param bool  $clone
     *
     * @return mixed
     *
     * User: Loong
     * Date: 2022/6/16
     * Time: 19:10
     */
    public function getJErrorWithData($state, $param = [], $data = [], $clone = true)
    {
        $state = strval($state);
        $msg = isset($this->errorCode[$state]) ? lang($this->errorCode[$state], $param) : '';
        return $clone ? $this->jsonTable->withMessage($msg, $state, $data) : $this->jsonTable->message($msg, $state, $data);
    }

    /**
     * 获取错误文本
     *
     * @param string|integer $state 错误码
     * @return string 返回错误文本
     */
    public function getErrText($state, $param = [])
    {
        $state = strval($state);
        return isset($this->errorCode[$state]) ? lang($this->errorCode[$state], $param) : '';
    }

    /**
     * 判断错误码是否存在
     *
     * @param int $state 错误码
     * @return bool
     */
    public function exists($state)
    {
        return isset($this->errorCode[strval($state)]);
    }
}
