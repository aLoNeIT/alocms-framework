<?php

namespace alocms\library\facade;

use alocms\library\util\ErrCode as ErrCodeUtil;
use think\Facade;

/**
 * @see \alocms\library\util\ErrCode
 * @mixed \alocms\library\util\ErrCode
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
