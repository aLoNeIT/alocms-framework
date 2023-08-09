<?php

declare(strict_types=1);

namespace alocms\controller;

use alocms\logic\Session as SessionLogic;

/**
 * 动态Api接口基类
 * @author alone <alone@alonetech.com>
 */
class DynamicApi extends Base
{
    /**
     * 会话逻辑类
     * @var SessionLogic
     */
    protected $sessionLogic = null;

    /** @inheritDoc */
    public function initialize(): void
    {
        parent::initialize();
        $this->sessionLogic = SessionLogic::instance();
    }

    public function index()
    {
    }

    public function read(int $id)
    {
    }

    public function save()
    {
    }

    public function update(int $id)
    {
    }

    public function delete(int $id)
    {
    }
    /**
     * 获取当前动态接口对应的字典数据
     *
     * @return void
     */
    public function dict()
    {
    }
}
