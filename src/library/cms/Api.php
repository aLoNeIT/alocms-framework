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
     * 
     * @param $name 文件名
     * @return string
     */
    public function show(string $name): string
    {
        $controller = $this->request->controller();
        $controller = str_replace('.', '/', strtolower($controller));
        $data = [];
        $this->beforeShow($controller, $name, $data);
        $content = ViewFacade::fetch("{$controller}/{$name}", $data);
        return $content;
    }
    /**
     * 用于在显示爷面前做特殊处理
     *
     * @return JsonTable
     */
    /**
     * 页面渲染前处理
     *
     * @param string $controller 控制器名
     * @param string $name 模板名
     * @param array $data 模板数据
     * @return JsonTable 返回jsonTable对象
     */
    public function beforeShow(string &$controller, string &$name, array &$data): JsonTable
    {
        return $this->jsonTable->success();
    }
    /**
     * 页面渲染后处理
     *
     * @param string $content 渲染后的内容
     * @return JsonTable 返回jsonTable对象
     */
    public function afterShow(string &$content): JsonTable
    {
        return $this->jsonTable->success();
    }
}
