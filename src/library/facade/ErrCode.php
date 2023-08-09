<?php

namespace alocms\facade;

use alocms\util\ErrCode as ErrCodeUtil;
use think\Facade;

/**
 * @see \alocms\util\ErrCode
 * @mixed \alocms\util\ErrCode
 * 
 * @method static array getError($state,array $param=[]) 获取数组格式的错误信息
 * @method static JsonTable getJError(string|integer $state,array $param=[],bool $clone=true) 获取JsonTable对象的错误信息
 * @method static JsonTable getJErrorWithData(string|integer $state,array $param=[],$data=null,bool $clone=true) 获取JsonTable对象的错误，包含data节点
 * @method static string getErrText(string|integer $state,array $param=[]) 获取错误文本信息
 * @method static bool exists(string|integer $state) 判断错误码是否存在
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
