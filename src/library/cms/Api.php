<?php

declare(strict_types=1);

namespace alocms\cms;

use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\util\JsonTable;
use think\facade\View as ViewFacade;


/**
 * Api接口基类
 */
class Api extends Base
{

    /**
     * 检查请求类型是否符合要求的类型
     *
     * @param $method 请求方法类型
     *
     * @return void
     */
    protected function checkMethod($method): void
    {
        if (strtoupper($this->request->method()) !== strtoupper($method)) {
            abort(json(ErrCodeFacade::getError(14)));
        }
    }

    /**
     * 用于显示对应模板文件
     * @param $name 文件名
     * @return mixed
     */
    public function show(string $name): string
    {
        $this->beforeShow();
        $controller = $this->request->controller();
        $controller = str_replace('.', '/', strtolower($controller));
        $content = ViewFacade::fetch("{$controller}/{$name}");
        $this->app->config->get('default_return_type', 'html');
        return $content;
    }

    public function beforeShow(): JsonTable
    {
        return $this->jsonTable->success();
    }
}
