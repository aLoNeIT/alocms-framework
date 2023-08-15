<?php

declare(strict_types=1);

namespace alocms\util;

/**
 * 标准交互数据格式
 * @author alone <alone@alonetech.com>
 */
class JsonTable
{
    /**
     * PHP序列化
     */
    const SERIALIZE_PHP = 1;
    /**
     * Json序列化
     */
    const SERIALIZE_JSON = 2;
    /**
     * 存储jtable数据
     *
     * @var array
     */
    protected $data = [];
    /**
     * json_encode时候的option参数
     *
     * @var integer
     */
    protected $jsonOption = JSON_UNESCAPED_UNICODE;
    /**
     * json_encode时的节点深度，默认512
     *
     * @var integer
     */
    protected $jsonDepth = 512;
    /**
     * 附带的异常对象
     *
     * @var \Throwable
     */
    protected $exception = null;
    /**
     * 序列化方式
     *
     * @var integer
     */
    protected $serializeType = JsonTable::SERIALIZE_JSON;
    /**
     * 构造函数，初始化数组
     */
    public function __construct()
    {
        $this->clear();
    }
    /**
     * 析构函数
     */
    public function __destruct()
    {
        unset($this->data);
    }
    /**
     * 当jtable被当做字符串输出时，将内部数据json_encode输出
     *
     * @return string
     */
    public function __toString()
    {
        $result = '';
        switch ($this->serializeType) {
            case JsonTable::SERIALIZE_PHP:
                $result = $this->toSerialize();
                break;
            case JsonTable::SERIALIZE_JSON:
                $result = $this->toJson();
                break;
        }
        return $result;
    }
    /**
     * JsonTable内存储的数据
     *
     * @return array 返回存储的数组数据
     */
    public function toArray(): array
    {

        return $this->filterData();
    }
    /**
     * 获取Json字符串
     *
     * @return string 返回Json字符串
     */
    public function toJson(): string
    {
        return json_encode($this->filterData(), $this->jsonOption);
    }
    /**
     * 获取序列化字符串
     *
     * @return string 返回序列化字符串
     */
    public function toSerialize(): string
    {
        return serialize($this->filterData());
    }

    /**
     * 获取过滤data节点空值后的数据
     *
     * @return array
     */
    public function filterData(): array
    {
        if (isset($this->data['data']) && ((\is_array($this->data['data']) || (\is_object($this->data['data']) && \is_countable($this->data['data'])))
            && 0 == count($this->data['data']))) {
            unset($this->data['data']);
        }
        return $this->data;
    }

    /**
     * 设置属性
     *
     * @param string $name 属性名
     * @param mixed $value 值
     * @return JsonTable
     */
    public function setProperty(string $name, $value): static
    {
        $properties = ['jsonoption', 'jsondepth', 'serializetype', 'exception'];
        if (in_array(strtolower($name), $properties)) {
            $this->$name = $value;
        }
        return $this;
    }
    /**
     * 获取属性值
     *
     * @param string $name 属性名
     * @return mixed
     */
    public function getProperty(string $name)
    {
        $properties = ['jsonoption', 'jsondepth', 'serializetype', 'exception'];
        if (in_array(strtolower($name), $properties)) {
            return $this->$name;
        }
    }
    /**
     * 获取JsonTable是否成功数据
     *
     * @return boolean
     */
    public function isSuccess(): bool
    {
        return 0 === $this->data['state'];
    }
    /**
     * 清空保存的数据
     *
     * @return void
     */
    public function clear(): void
    {
        $this->data = [
            'state' => 0,
            'msg' => 'success',
        ];
    }
    /**
     * 设置JsonTable数据
     *
     * @param integer|string $state 状态码
     * @param string|array $msg 消息体
     * @param mix $data 数据体
     * @return JsonTable 返回自身
     */
    protected function setData($state = 0, $msg = 'success', $data = null): static
    {
        $this->clear();
        $this->data = [
            'state' => intval($state),
            'msg' => $msg,
        ];
        if (!is_null($data)) {
            $this->data['data'] = $data;
        }
        return $this;
    }
    /**
     * 使用数组进行设置
     *
     * @param array $data 包含jsonTable格式数组
     * @return JsonTable
     */
    public function setByArray(array $data): static
    {
        $this->clear();
        $this->data['state'] = $data['state'] ?? 0;
        $this->data['msg'] = $data['msg'] ?? 'success';
        if (isset($data['data']) && !\is_null($data['data']) && !empty($data['data'])) {
            $this->data['data'] = $data['data'];
        }
        return $this;
    }
    /**
     * 获取成功数据
     *
     * @param mix $msg    消息体
     * @param mix $data    数据体
     * @return JsonTable 返回当前对象
     */
    public function success($msg = 'success', $data = null): static
    {
        return $this->setData(0, $msg, $data);
    }
    /**
     * 克隆一个包含成功信息的JsonTable对象
     *
     * @param mixed $msg 消息体
     * @param mixed $data 数据体
     * @return JsonTable 返回克隆后的全新JsonTable对象
     */
    public function withSuccess($msg = 'success', $data = null): static
    {
        return clone $this->setData(0, $msg, $data);
    }
    /**
     * 获取一个包含成功数据的JsonTable对象
     *
     * @param mixed $data 数据体
     * @return JsonTable
     */
    public function successByData($data): static
    {
        return $this->setData(0, 'success', $data);
    }
    /**
     * 克隆一个包含成功数据的JsonTable对象
     *
     * @param mixed $data 数据体
     * @return JsonTable
     */
    public function withSuccessByData($data): static
    {
        return clone $this->setData(0, 'success', $data);
    }
    /**
     * 克隆一个包含错误信息的JsonTable对象
     *
     * @param mixed $msg 消息体
     * @param integer $state 状态码
     * @param mixed $data 数据体
     * @return JsonTable 返回克隆后的全新JsonTable对象
     */
    public function withError($msg = 'error', $state = 1, $data = null): static
    {
        return clone $this->setData($state, $msg, $data);
    }
    /**
     * 获取组装好的错误数据
     *
     * @param integer $state 状态码
     * @param mixed $msg 消息体
     * @param mixed $data 数据体
     * @return JsonTable 返回当前对象
     */
    public function error($msg = 'error', $state = 1, $data = null): static
    {
        return $this->setData($state, $msg, $data);
    }
    /**
     * 设置包含自定义信息的JsonTable对象
     *
     * @param mixed $msg 消息体或包含相关节点的数组
     * @param integer $state 状态码
     * @param mixed $data 数据体
     * @return JsonTable 返回克隆后的全新JsonTable对象
     */
    public function withMessage($msg, $state = 0, $data = null): static
    {
        return clone $this->setData($state, $msg, $data);
    }
    /**
     * 设置JsonTable数据
     *
     * @param mixed $msg 消息体或包含相关节点的数组
     * @param integer $state 状态码
     * @param mixed $data 数据体
     * @return JsonTable 返回当前对象
     */
    public function message($msg, $state = 0, $data = null): static
    {
        if ($msg instanceof JsonTable) {
            return $this->setData($msg->state, $msg->msg, $msg->data['data'] ?? []);
        } else {
            return $this->setData($state, $msg, $data);
        }
    }
    /**
     * 魔术方法，用于支持使用对象的方式访问数据内容
     *
     * @param string $name        jtable内的键名
     * @return integer|string|array   返回获取到的数据
     */
    public function __get($name)
    {
        $value = '';
        switch ($name) {
            case 'state':
                $value = intval($this->data['state']);
                break;
            case 'msg':
                $value = $this->data['msg'];
                break;
            case 'data':
                $value = $this->data['data'] ?? null;
                break;
            default:
                $value = $this->data['data'][$name] ?? null;
                break;
        }
        return $value;
    }
    /**
     * 设置data数据
     *
     * @param string $name 键名
     * @param mixed $value 键值
     */
    public function __set($name, $value)
    {
        //禁止直接修改state和msg
        if ('data' == $name) {
            $this->data['data'] = $value;
        } else {
            $this->data['data'][$name] = $value;
        }
    }
}
