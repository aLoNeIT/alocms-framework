<?php

declare(strict_types=1);

namespace alocms\middleware\privilege;

use alocms\logic\Privilege as PrivilegeLogic;
use alocms\logic\Session as SessionLogic;
use alocms\middleware\Base;
use alocms\Request;
use alocms\util\JsonTable;

/**
 * 系统权限处理
 * @author 王阮强 <wangruanqiang@youzhibo.cn>
 * @date 2023-08-09
 */
class Cms extends Base
{
    /** @inheritDoc */
    protected function before(Request $request): JsonTable
    {
        $appType = $request->appType();
        /** @var SessionLogic $sessionLogic */
        $sessionLogic = SessionLogic::instance();
        //是否不校验session
        $sessionPassed = false;
        if (false === $request->checkWhiteList('session')) {
            $jResult = $sessionLogic->check($appType);
            if (!$jResult->isSuccess()) {
                return $jResult;
            }
            $sessionPassed = true;
        }
        //是否是权限白名单
        if (false === $sessionPassed && false === $request->checkWhiteList('privilege')) {
            /** @var PrivilegeLogic $privilegeLogic */
            $privilegeLogic = PrivilegeLogic::instance();
            // 检查用户权限
            return $privilegeLogic->check();
        }
        // 返回检查成功
        return $this->jsonTable->success();
    }
}
