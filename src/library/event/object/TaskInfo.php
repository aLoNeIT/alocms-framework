<?php

declare(strict_types=1);

namespace alocms\library\event\object;

/**
 * 后台任务信息类
 *
 * @author 王阮强 <wangruanqiang@youzhibo.cn>
 * @date 2020-12-11
 */
class TaskInfo
{
    protected $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
    }
}
