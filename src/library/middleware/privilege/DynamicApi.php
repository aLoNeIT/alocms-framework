<?php

declare(strict_types=1);

namespace alocms\middleware\privilege;

use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\logic\Session as SessionLogic;
use alocms\middleware\Base;
use alocms\model\Menu;
use alocms\Request;
use alocms\util\JsonTable;

/**
 * 动态接口中间件，处理动态接口权限相关
 */
class DynamicApi extends Base
{
    /** @inheritDoc */
    protected function before(Request $request): JsonTable
    {
        // 获取当前请求的uri
        $uri = $request->baseUrl();
        // 查询当前uri对应的菜单
        $menu = Menu::instance()->getByUri($uri, $request->appType())->find();
        if (\is_null($menu)) {
            return ErrCodeFacade::getJError(25, [
                'name' => '菜单数据'
            ]);
        }
        // 获取当前菜单对应的功能权限
        $function = $menu->functions()->where('fn_menu_code', 'like', '%00')->find();
        if (\is_null($function)) {
            return ErrCodeFacade::getJError(25, [
                'name' => '功能数据'
            ]);
        }
        $fnCode = $function->fn_code;
        // 获取当前用户的菜单权限
        /** @var SessionLogic $sessionLogic */
        $sessionLogic = SessionLogic::instance();
        if (!($jResult = $sessionLogic->getFunction())->isSuccess()) {
            return $jResult;
        }
        $functions = $jResult->data;
        // 判断当前用户是否有权限
        if (!\in_array($fnCode, $functions)) {
            return ErrCodeFacade::getJError(81);
        }
        return $this->jsonTable->success();
    }
}
