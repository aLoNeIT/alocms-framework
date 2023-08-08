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
        $this->sessionLogic = $this->app->get('SessionLogic');
    }

    public function index()
    {
    }

    public function save()
    {
    }

    public function update()
    {
    }

    public function delete()
    {
    }
}
