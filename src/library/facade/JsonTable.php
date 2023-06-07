<?php

declare(strict_types=1);

namespace alocms\library\facade;

use alocms\library\util\JsonTable as JsonTableUtil;
use think\Facade;

/**
 * @see \alocms\library\util\JsonTable
 * @mixin \alocms\library\util\JsonTable
 * @author aLoNe.Adams.K <alone@alonetech.com>
 * 
 * @method clear static
 * @method getData($state=0,$msg='success',$data=[]) static
 * @method getJson($state=0,$msg='success',$data=[]) static
 * @method success($msg='success',$data=[]) static
 * @method error($state=1,$msg='error',$data=[]) static
 */
class JsonTable extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return JsonTableUtil::class;
    }
}
