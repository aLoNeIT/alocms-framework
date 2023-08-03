<?php

declare(strict_types=1);

namespace alocms\extend\think\response;

use alocms\util\JsonTable;
use think\Cookie;
use think\Response;

/**
 * JsonTable Response
 * 暂时无用
 *
 * @author 王阮强 <wangruanqiang@youzhibo.cn>
 * @date 2020-11-20
 */
class Jtable extends Response
{

    protected $contentType = 'application/json';

    public function __construct(Cookie $cookie, $data = '', int $code = 200)
    {
        $this->init($data, $code);
        $this->cookie = $cookie;
    }

    /**
     * 处理数据
     * @access protected
     * @param  mixed $data 要处理的数据
     * @return string
     * @throws \Exception
     */
    protected function output($data): string
    {
        try {
            if (!($data instanceof JsonTable)) {
                throw new \InvalidArgumentException(lang('response_type_error'));
            }

            return $data->toJson();
        } catch (\Throwable $e) {
            if ($e->getPrevious()) {
                throw $e->getPrevious();
            }
            throw $e;
        }
    }
}
