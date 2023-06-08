<?php

namespace alocms\util;

use think\facade\Config;

/**
 *  错误码处理类
 * 
 * @author alone <alone@alonetech.com>
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
     * @param string|integer $state 错误状态码
     * @param array $param 额外信息参数
     * @param boolean $clone 是否克隆
     * @return JsonTable 返回包含错误信息的JsonTable对象
     */
    public function getJError($state, array $param = [], bool $clone = true): JsonTable
    {
        return $this->getJErrorWithData($state, $param, null, $clone);
    }

    /**
     * 获取JsonTable对象的错误 包含data
     *
     * @param string|integer $state 错误状态码
     * @param array $param 额外信息参数
     * @param mixed $data 错误附带信息
     * @param boolean $clone 是否克隆
     * @return JsonTable 返回包含错误信息的JsonTable对象
     */
    public function getJErrorWithData($state, array $param = [], $data = null, bool $clone = true): JsonTable
    {
        $state = strval($state);
        $msg = isset($this->errorCode[$state]) ? lang($this->errorCode[$state], $param) : '';
        return $clone ? $this->jsonTable->withMessage($msg, $state, $data) : $this->jsonTable->message($msg, $state, $data);
    }

    /**
     * 获取错误文本
     *
     * @param string|integer $state 错误码
     * @param array $param 额外信息参数
     * @return string 返回错误文本
     */
    public function getErrText($state, array $param = []): string
    {
        $state = strval($state);
        return isset($this->errorCode[$state]) ? lang($this->errorCode[$state], $param) : '';
    }

    /**
     * 判断错误码是否存在
     *
     * @param string|integer $state 错误码
     * @return boolean 返回错误码是否存在的布尔结果
     */
    public function exists($state): bool
    {
        return isset($this->errorCode[strval($state)]);
    }
}
