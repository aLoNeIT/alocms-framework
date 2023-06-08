<?php

declare(strict_types=1);

namespace alocms\util;

use think\App;
use think\helper\Arr;
use think\Manager as ManagerBase;

/**
 * 代理管理类，子类只需要填写$name和$type即可
 * @author 王阮强 <wangruanqiang@hongshanhis.com>
 * @date 2022-06-17
 */
class Manager extends ManagerBase
{
    /**
     * 类库名，小写，关系到配置文件，需要类库目录、配置文件保持一致
     *
     * @var string
     */
    protected $name = '';
    /**
     * 默认驱动名称
     *
     * @var string
     */
    protected $type = null;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->namespace = "\\alocms\\extend\\{$this->name}\\driver\\";
    }

    /**
     * 默认驱动
     * @return string|null
     */
    public function getDefaultDriver()
    {
        return $this->getConfig('default');
    }

    /**
     * 获取缓存配置
     * @access public
     * @param string|null $name 名称
     * @param mixed|null $default 默认值
     * @return mixed
     */
    public function getConfig(string $name = null, $default = null)
    {
        if (!is_null($name)) {
            return $this->app->config->get("{$this->name}.{$name}", $default);
        }

        return $this->app->config->get($this->name);
    }

    /**
     * 获取驱动配置
     * @param string $store 驱动名称
     * @param string $name 配置键名
     * @param mixed|null   $default 默认值
     * @return mixed
     */
    public function getStoreConfig(string $store, string $name = null, $default = null)
    {
        if ($config = $this->getConfig("stores.{$store}")) {
            // 获取global配置，用于合并至当前驱动配置内
            $globalConfig = $this->getConfig('global', []);
            $config = \array_merge($globalConfig, $config);
            return Arr::get($config, $name, $default);
        }

        throw new \InvalidArgumentException("Store [$store] not found.");
    }

    protected function resolveType(string $name)
    {
        return $this->getStoreConfig($name, 'type', $this->type);
    }

    protected function resolveConfig(string $name)
    {
        return $this->getStoreConfig($name);
    }

    /**
     * 连接或者切换缓存
     * 
     * @param string $name 连接配置名
     * @return Driver
     */
    public function store(string $name = null)
    {
        return $this->driver($name);
    }

    /**
     * 方法名不存在时，调用实例对象内方法
     *
     * @param string $method 方法名
     * @param array $args 参数数组
     *
     * @return mixed 返回实例对象执行结果
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->store(), $method], $args);
    }
}
