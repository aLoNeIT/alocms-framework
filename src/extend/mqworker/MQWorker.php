<?php

declare(strict_types=1);

namespace alocms\extend\mqworker;

use alocms\library\util\Manager;
use think\helper\Arr;

/**
 * MQ打工人，用于封装不同MQ之间的差异
 * @author 王阮强 <wangruanqiang@hongshanhis.com>
 * @date 2022-04-08
 */
class MQWorker extends Manager
{
    /**
     * 类库名，小写，关系到配置文件，需要类库目录、配置文件保持一致
     *
     * @var string
     */
    protected $name = 'filestorage';
    /**
     * 默认驱动名称
     *
     * @var string
     */
    protected $type = 'local';
}
