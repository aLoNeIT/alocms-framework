<?php

declare(strict_types=1);

namespace alocms\facade;

use alocms\util\JsonTable as JsonTableUtil;
use think\Facade;

/**
 * @see \alocms\util\JsonTable
 * @mixin \alocms\util\JsonTable
 * @author aLoNe.Adams.K <alone@alonetech.com>
 * 
 * @method static void clear()
 * @method static string toJson()
 * @method static array toArray()
 * @method static JsonTableUtil success($msg = 'success', $data = null)
 * @method static JsonTableUtil error($msg = 'error', $state = 1, $data = null)
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
