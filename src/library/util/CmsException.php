<?php

declare(strict_types=1);

namespace alocms\util;

/**
 * 统一异常类
 * @author alone <alone@alonetech.com>
 */
class CmsException extends \Exception
{
    /**
     * 异常附加信息
     *
     * @var array
     */
    protected $data = null;
    /**
     * 错误码
     *
     * @var integer
     */
    protected $state = 1;
    /**
     * 重写构造函数
     *
     * @param string|JsonTable $msg 错误信息
     * @param integer $state 错误码
     * @param array $data 附加信息
     * @param Throwable $previous 上一个可抛出异常对象
     */
    public function __construct($msg, int $state = 1, ?array $data = null, ?\Throwable $previous = null)
    {
        if ($msg instanceof JsonTable) {
            $previous = $previous ?: $msg->getProperty('exception');
            parent::__construct($msg->msg, $msg->state, $previous);
            $this->data = $msg->data;
            $this->state = $msg->state;
        } else {
            parent::__construct($msg, $state, $previous);
            $this->data = $data;
            $this->state = $state;
        }
    }
    /**
     * 获取附带信息
     *
     * @return array|null 返回附带信息
     */
    final public function getData(): ?array
    {
        return $this->data;
    }
    /**
     * 获取错误码
     *
     * @return integer 返回错误码
     */
    final public function getState(): int
    {
        return $this->state;
    }
}
