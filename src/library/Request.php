<?php

declare(strict_types=1);

namespace alocms\library;

// 应用请求对象类
class Request extends \think\Request
{
    protected $filter = ['htmlspecialchars'];

    public function __construct()
    {
        parent::__construct();
        // 通过配置文件读取代理服务器地址
        $proxyServerIp = config('system.proxy_server');
        if (!\is_null($proxyServerIp)) {
            $this->proxyServerIp = \explode(',', $proxyServerIp);
        }
    }

    /**
     * 获取应用类型
     *
     * @return integer
     */
    public function appType(): int
    {
        $appTypeMap = config('system.app_type');
        return intval($appTypeMap[app('http')->getName()] ?? 3);
    }
    /**
     * 获取每次请求唯一标识
     *
     * @return string
     */
    public function requestId(): string
    {
        if (!isset($this->requestId)) {
            $this->requestId = getmypid() . '-' . time() . '-' . \makeUUID();
        }
        return $this->requestId;
    }


    /**
     * 接口白名单
     *
     * @param $type
     * @return bool
     */
    public function getCheck($type): bool
    {
        $model = app('http')->getName();
        $controller = request()->controller();
        $action = request()->action();
        $url = $model . '/' . $controller . '/' . $action;
        $route = config('system.white_list');
        return in_array($url, $route[$type]);
    }

    /**
     * 设置请求数据
     *
     * @param array $param 新增的数据
     * @return self
     */
    public function withParam(array $param): static
    {
        $this->param = array_merge($this->param, $param);
        return $this;
    }

    /**
     * 获取客户端IP地址
     * @access public
     * @return string
     */
    public function ip(): string
    {
        if (!empty($this->realIP)) {
            return $this->realIP;
        }

        // 获取ip
        $forwardedFor = $this->server('HTTP_X_FORWARDED_FOR');
        $ips = \explode(',', $forwardedFor);
        if (isset($ips[0])) {
            $this->realIP = $ips[0];
        }
        if (!$this->isValidIP($this->realIP)) {
            $this->realIP = '0.0.0.0';
        }

        return $this->realIP;
    }
}
