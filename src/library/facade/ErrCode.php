<?php

namespace alocms\facade;

use alocms\util\ErrCode as ErrCodeUtil;
use think\Facade;

/**
 * @see \alocms\util\ErrCode
 * @mixed \alocms\util\ErrCode
 */
class ErrCode extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return ErrCodeUtil::class;
    }
}
