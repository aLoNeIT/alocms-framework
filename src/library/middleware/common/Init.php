<?php

declare(strict_types=1);

namespace alocms\middleware\common;

use alocms\middleware\Base;
use alocms\Request;
use alocms\util\JsonTable;
use think\Response;

/**
 * 系统权限全局预处理
 * @author 王阮强 <wangruanqiang@youzhibo.cn>
 * @date 2020-10-15
 */
class Init extends Base
{
    /**
     * 会话对象
     *
     * @var \think\Session
     */
    protected $session = null;

    /** @inheritDoc */
    protected function initialize(): void
    {
        parent::initialize();
        $this->session = $this->app->make('session');
    }
    /** @inheritDoc */
    protected function before(Request $request): JsonTable
    {
        // 初始化session
        // flash插件跨域问题
        $varSessionId = $this->app->config->get('session.var_session_id');
        // 获取session名称
        $sessionName = $this->session->getName();
        // 优先从header取session，然后request、然后cookie
        if ($request->header($sessionName)) {
            $sessionId = $request->header($sessionName);
        } else if ($varSessionId && $request->request($varSessionId)) {
            $sessionId = $request->request($varSessionId);
        } else {
            $sessionId = $request->cookie($sessionName);
        }
        // 给本次请求设置sessionid
        if ($sessionId) {
            $this->session->setId($sessionId);
        }
        // 初始化
        $this->session->init();
        $request->withSession($this->session);
        // session初始化完毕
        return $this->jsonTable->success();
    }
    /** @inheritDoc */
    protected function after(Request $request, Response $response): JsonTable
    {
        // 处理session相关
        $response->setSession($this->session);
        $sessionName = $this->session->getName();
        $sessionId = $this->session->getId();
        // 判断session应该写入到header还是cookie
        if ($request->header($sessionName)) {
            $response->header([
                $sessionName => $sessionId,
            ]);
        } else {
            $this->app->cookie->set($sessionName, $sessionId);
        }
        // session 处理完毕
        return $this->jsonTable->success();
    }
    /** @inheritDoc */
    public function end(Response $response)
    {
        // 请求结束统一写入session
        $this->session->save();
    }
}
