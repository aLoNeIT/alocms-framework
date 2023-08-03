<?php

declare(strict_types=1);

namespace alocms\extend\think\cache\driver;

use think\cache\driver\Redis;

/**
 * redis集群类，内部兼容redis单机
 * redis扩展方法对函数大小写不敏感，这里为了美观实用驼峰方法
 */
class RedisCluster extends Redis
{
    /**
     * 重写构造函数，使其支持cluster参数
     *
     * @param array $options 缓存参数
     */
    public function __construct(array $options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }

        if (extension_loaded('redis')) {
            if (isset($this->options['cluster']) && true == $this->options['cluster']) {
                $this->handler = new \RedisCluster($this->options['cluster_name'], $this->options['host'], $this->options['timeout'], $this->options['read_timeout'], $this->options['persistent']);
                if ('' != $this->options['password']) {
                    $this->handler->auth($this->options['password']);
                }
            } else {
                $this->handler = new \Redis;

                if ($this->options['persistent']) {
                    $this->handler->pconnect($this->options['host'], $this->options['port'], $this->options['timeout'], 'persistent_id_' . $this->options['select']);
                } else {
                    $this->handler->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
                }

                if ('' != $this->options['password']) {
                    $this->handler->auth($this->options['password']);
                }

                if (0 != $this->options['select']) {
                    $this->handler->select($this->options['select']);
                }
            }
        } elseif (class_exists('\Predis\Client')) {
            $params = [];
            foreach ($this->options as $key => $val) {
                if (in_array($key, ['aggregate', 'cluster', 'connections', 'exceptions', 'prefix', 'profile', 'replication', 'parameters'])) {
                    $params[$key] = $val;
                    unset($this->options[$key]);
                }
            }

            if ('' == $this->options['password']) {
                unset($this->options['password']);
            }

            $this->handler = new \Predis\Client($this->options, $params);

            $this->options['prefix'] = '';
        } else {
            throw new \BadFunctionCallException('not support: redis');
        }
    }

    public function getCache(string $name)
    {

        $key = $this->getCacheKey($name);

        $str = $this->handler->get($key);

        $exp = explode('|', $str);

        $str = ltrim($str, $exp[0] . '|');

        return $this->unserialize($str);
    }
    //list相关操作开始

    /**
     * 向队列的左侧添加一个元素
     *
     * @param string $name 队列名
     * @param mix $value 数据
     * @param mix $more 变长参数，数据
     * @return mixed 失败返回false，成功返回队列长度
     */
    public function lPush(string $name, $value, ...$more)
    {
        $key = $this->getCacheKey($name);
        $value = $this->serialize($value);
        foreach ($more as &$arg) {
            $arg = $this->serialize($arg);
        }
        return $this->handler->lPush($key, $value, ...$more);
    }

    /**
     * 向队列的右侧侧添加一个元素
     *
     * @param string $name 队列名
     * @param mix $value 数据
     * @param mix $more 变长参数，数据
     * @return mixed 失败返回false，成功返回队列长度
     */
    public function rPush(string $name, $value, ...$more)
    {
        $key = $this->getCacheKey($name);
        $value = $this->serialize($value);
        foreach ($more as &$arg) {
            $arg = $this->serialize($arg);
        }
        return $this->handler->rPush($key, $value, ...$more);
    }

    /**
     * 从队列右侧取出一个元素
     *
     * @param string $name 队列名
     * @return mixed 成功返回取出的值，失败返回false
     */
    public function rPop(string $name)
    {
        $key = $this->getCacheKey($name);
        $value = $this->handler->rPop($key);
        if (false === $value) {
            return false;
        }
        return $this->unserialize($value);
    }

    /**
     * 从队列左侧取出一个元素
     *
     * @param string $name 键名
     * @return mixed 失败返回false，成功返回数据
     */
    public function lPop(string $name)
    {
        $key = $this->getCacheKey($name);
        $value = $this->handler->lPop($key);
        if (false === $value) {
            return false;
        }
        return $this->unserialize($value);
    }

    /**
     * 队列长度
     *
     * @param string $name 键名
     * @return mixed 失败返回false，成功返回队列长度
     */
    public function lLen($name)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->lLen($key);
    }

    /**
     * 删除指定值value
     *
     * @param string $name 队列名
     * @param mix $value 数据
     * @param mix $ 变长参数，数据
     * @return mixed 失败返回false，成功返回队列长度
     */
    public function lRem($name, $value, $count = 1)
    {
        $key = $this->getCacheKey($name);
        $value = $this->serialize($value);
        return $this->handler->lRem($key, $value, $count);
    }

    //list相关操作结束

    //string相关操作开始

    /**
     * 当键不存在时设置一个键值
     *
     * @param string $name 键名
     * @param mixed|callback $value 键值，可以使回调函数
     * @param int $expire 加锁有效时长，单位秒
     * @return mixed 返回bool或者回调函数执行结果
     */
    public function setnx(string $name, $value, int $expire = 5)
    {
        $key = $this->getCacheKey($name);
        $config = ['nx'];
        if ($expire > 0) {
            $config['ex'] = $expire;
        }

        if ($value instanceof \Closure) {
            $callback = $value;
            $value = $key . '_' . uniqid((string)\mt_rand(), true);
            $result = $this->handler->set($key, $value, $config);
            if (false !== $result) {
                //设置成功
                try {
                    $result = $callback();
                } finally {
                    $val = $this->handler->get($key);
                    if ($value === $val) {
                        $this->handler->del($key);
                    }
                }
            }
            return $result;
        } else {
            $value = $this->serialize($value);
            return $this->handler->set($key, $value, $config);
        }
    }
    /**
     * 在指定时间内尝试抢占锁
     *
     * @param string $name 键名
     * @param mix|Cloure $value 键值或者闭包函数，闭包函数返回值不能为false
     * @param integer $wait 等待时间
     * @param integer $expire 锁有效时间
     * @return mix 返回bool或者闭包自身返回的值
     */
    public function setnxAndWait(string $name, $value, int $wait = 5, int $expire = 30)
    {
        $startTime = time();
        $result = false;
        // 尝试抢占锁，若失败则休眠0.1秒继续抢占，超过等待最大时间则退出
        while (false === ($result = $this->setnx($name, $value, $expire))) {
            if ($startTime + $wait < time()) {
                return false;
            }
            \usleep(100000);
        }
        return $result;
    }

    /**
     * 设置指定键的值，并返回旧值
     *
     * @param string $name 键名
     * @param mix $value 数据
     * @return mix 失败返回false，键存在时返回旧值，不存在时返回false
     */
    public function getSet(string $name, $value)
    {
        $key = $this->getCacheKey($name);
        $value = $this->serialize($value);
        return $this->handler->getSet($key, $value);
    }
    /**
     * string 批量获取指定键的值
     *
     * @param array $names 键名下标数组
     * @return mix 失败返回false，成功返回下标数组
     */
    public function mGet(array $names)
    {
        //处理键名
        for ($i = 0; $i < count($names); $i++) {
            $names[$i] = $this->getCacheKey($names[$i]);
        }
        //获取数据
        $data = $this->handler->mGet($names);
        $result = [];
        for ($i = 0; $i < count($names); $i++) {
            if (false !== $data[$i]) {
                $result[$names[$i]] = $this->unserialize($data[$i]);
            }
        }
        return empty($result) ? false : $result;
    }
    /**
     * string 批量设置数据
     *
     * @param array $data 数据，索引数组
     * @return mix 失败返回false，成功返回true
     */
    public function mSet(array $data)
    {
        //处理键名和数据
        $array = [];
        foreach ($data as $key => $value) {
            $array[$this->getCacheKey($key)] = $this->serialize($value);
        }
        return $this->handler->mSet($array);
    }

    /**
     * string 获取存储的字符串长度
     *
     * @param string $name 键名
     * @return mix 失败返回false，成功返回字符串长度，键不存在时返回0
     */
    public function strlen($name)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->strlen($key);
    }

    //string相关操作结束

    //hash相关操作开始

    /**
     * hash 元素添加
     *
     * @param string $name 键名
     * @param string $field 字段名
     * @param mix $value 数据
     * @return mix 失败返回false，成功新建返回1，更新返回0
     */
    public function hSet($name, $field, $value)
    {
        $key = $this->getCacheKey($name);
        $value = $this->serialize($value);
        return $this->handler->hSet($key, $field, $value);
    }

    /**
     * hash 元素获取
     *
     * @param string $name 键名
     * @param string $field 字段名
     * @return mixed 失败返回false，成功返回缓存的数据
     */
    public function hGet($name, $field)
    {
        $key = $this->getCacheKey($name);
        $value = $this->handler->hGet($key, $field);
        return $this->unserialize($value);
    }

    /**
     * hash 批量添加元素
     * @param string $name 键名
     * @param array $data key=>value形式的数组
     * @return bool 失败返回false，成功返回true
     */
    public function hmSet($name, array $data)
    {
        $key = $this->getCacheKey($name);
        foreach ($data as $k => &$value) {
            $value = $this->serialize($value);
        }
        return $this->handler->hmSet($key, $data);
    }

    /**
     * hash 获取多个字段值
     * @param string $name 键名
     * @param array $fields 字段名数组
     * @return mixed 失败返回false，成功按照字段给定顺序返回值
     */
    public function hmGet($name, array $fields)
    {
        $key = $this->getCacheKey($name);
        $data = $this->handler->hmGet($key, $fields);
        if (false === $data) {
            return false;
        }
        foreach ($data as $key => &$value) {
            $value = $this->unserialize($value);
        }
        return $data;
    }

    /**
     * hash 获取元素数量
     *
     * @param string $name 键名
     * @return mixed 失败返回false，键名不存在返回0，成功返回元素个数
     */
    public function hLen($name)
    {
        $key = $this->getCacheKey($name);
        $len = $this->handler->hLen($key);
        return $len;
    }

    /**
     * hash 删除指定字段
     *
     * @param string $name 键名
     * @param string $field 字段名
     * @param string $more 变长参数，字段名
     * @return mixed 失败返回false，成功返回被删除的字段数量
     */
    public function hDel($name, $field, ...$more)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->hDel($key, $field, ...$more);
    }

    /**
     * hash 判断指定字段是否存在
     *
     * @param string $name 键名
     * @param string $field 字段名
     * @return mixed 失败返回false，字段存在返回1，键名或字段不存在返回0
     */
    public function hExists($name, $field)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->hExists($key, $field);
    }

    /**
     * hash 获取所有字段的值
     *
     * @param string $name 键名
     * @return mix 失败返回false，成功返回字段值数组
     */
    public function hVals($name)
    {
        $key = $this->getCacheKey($name);
        $data = $this->handler->hvals($key);
        if (false === $data) {
            return false;
        }
        foreach ($data as $key => &$value) {
            $value = $this->unserialize($value);
        }
        return $data;
    }

    /**
     * hash 返回所有字段名
     *
     * @param string $name 键名
     * @return mix 失败返回false，成功返回字段名数组
     */
    public function hKeys($name)
    {
        $key = $this->getCacheKey($name);
        $value = $this->handler->hKeys($key);
        return $value;
    }

    /**
     * hash 使用索引行驶返回字段及值
     * 性能差（单线程的！当它处理一个请求时其他的请求只能等着，必须遍历每个字段来获取数据）
     * 移植性差，慎用 巨坑
     *
     * @param string $name 键名
     * @return mix 失败返回false，成功返回field=>value的索引数组
     */
    public function hGetAll($name)
    {
        $key = $this->getCacheKey($name);
        $data = $this->handler->hGetAll($key);
        if (false === $data) {
            return false;
        }
        foreach ($data as $field => &$value) {
            $value = $this->unserialize($value);
        }
        return $data;
    }

    /**
     * hash 对字段值自增
     *
     * @param string $name 键名
     * @param string $field 字段名
     * @param integer $value 增加的值，默认值1
     * @return mix 失败返回false，成功返回自增后的值
     */
    public function hIncrBy($name, $field, int $value = 1)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->hIncrBy($key, $field, $value);
    }

    /**
     * hash 对字段值自增一个浮点数
     *
     * @param string $name 键名
     * @param string $field 字段名
     * @param float $value 增量值，默认为1.0
     * @return bool 失败返回false，成功返回字段中的值
     */
    public function hIncrByFloat($name, $field, float $value = 1.0)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->hIncrByFloat($key, $field, $value);
    }

    //hash相关操作结束

    //key相关操作开始

    /**
     * 查找所有符合给定模式pattern的键名
     *
     * @param string $pattern 匹配模式
     * @return mix 失败返回false，成功返回键名数组
     */
    public function keys($pattern)
    {
        $key = $this->getCacheKey($pattern);
        $value = $this->handler->keys($key);
        return $value;
    }

    /**
     * 判断缓存类型
     * @param string $name 键名
     * @return mix 失败返回false，成功返回整型值
     *
     * none: Redis::REDIS_NOT_FOUND   0
     * string: Redis::REDIS_STRING     1
     * set: Redis::REDIS_SET           2
     * list: Redis::REDIS_LIST         3
     * zset: Redis::REDIS_ZSET         4
     * hash: Redis::REDIS_HASH         5
     */
    public function type($name)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->type($key);
    }

    /**
     * 清除缓存
     *
     * @return boolean
     */
    public function clear(): bool
    {
        $this->writeTimes++;
        if (isset($this->options['cluster']) && true == $this->options['cluster']) {
            foreach ($this->handler->_masters() as $master) {
                if (false === $this->handler->flushDb($master)) {
                    return false;
                }
            }
            return true;
        } else {
            return $this->handler->flushDb();
        }
    }

    /**
     * 使用迭代器遍历所有键
     * @param string $pattern 匹配模式
     * @param Callable $callback 回调函数
     * @param integer $count 每次返回匹配到的数量
     * @return bool 成功返回true，失败返回false，回调函数返回非true则本次调用终止且返回false
     *
     */
    public function scan($pattern, callable $callback, $count = 10000)
    {
        $key = $this->getCacheKey($pattern);
        //判断是否集群模式，如果是集群模式则需要手动获取每个节点信息，然后将指令发给指定节点
        if (isset($this->options['cluster']) && true == $this->options['cluster']) {
            foreach ($this->handler->_masters() as $master) {
                $iterator = null;
                do {
                    //获取scan到的键名数组
                    $data = $this->handler->scan($iterator, $master, $key, $count);
                    if (false !== $data) {
                        foreach ($data as $value) {
                            //遍历每个键名，使用回调函数进行处理
                            $result = $callback($value);
                            //只有回调函数明确返回true才会继续执行下去
                            if (true !== $result) {
                                return false;
                            }
                        }
                    }
                } while ($iterator > 0);
            }
        } else {
            $iterator = null;
            do {
                //获取scan到的键名数组
                $data = $this->handler->scan($iterator, $key, $count);
                if (false !== $data) {
                    foreach ($data as $value) {
                        //遍历每个键名，使用回调函数进行处理
                        $result = $callback($value);
                        //只有回调函数明确返回true才会继续执行下去
                        if (true !== $result) {
                            return false;
                        }
                    }
                }
            } while ($iterator > 0);
        }
        return true;
    }

    /**
     * 设置键的有效期
     *
     * @param string $name 键名
     * @param integer|DateTime $time 有效期
     * @return mix 失败返回false，成功设置返回1，设置失败返回0
     */
    public function expire($name, $time)
    {
        $key = $this->getCacheKey($name);
        $time = $this->getExpireTime($time);
        return $this->handler->expire($key, $time);
    }

    /**
     * 返回键的剩余有效期
     *
     * @param string $name 键名
     * @return mix 失败返回false，成功返回剩余有效期，单位秒，-1代表永久有效，-2代表已过期
     */
    public function ttl($name)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->ttl($key);
    }

    /**
     * 修改键名
     *
     * @param string $source 旧键名
     * @param string $destination 新键名
     * @return bool 失败返回false，成功返回true
     */
    public function rename($source, $destination)
    {
        $keySource = $this->getCahceKey($source);
        $keyDestination = $this->getCacheKey($destination);
        return $this->handler->rename($keySource, $keyDestination);
    }

    /**
     * 当新建名不存在时修改键名
     *
     * @param string $source 旧键名
     * @param string $destination 新键名
     * @return mix 失败返回false，修改成功返回1，修改失败返回0
     */
    public function renamenx($source, $destination)
    {
        $keySource = $this->getCahceKey($source);
        $keyDestination = $this->getCacheKey($destination);
        return $this->handler->renamenx($keySource, $keyDestination);
    }

    //key相关操作结束

    //set相关操作开始

    /**
     * set 添加元素
     *
     * @param string $name 键名
     * @param mix $value 数据
     * @param mix $more 变长参数，数据
     * @return mix 失败返回false，成功返回添加元素数量
     */
    public function sAdd($name, $value, ...$more)
    {
        $key = $this->getCacheKey($name);
        $value = $this->serialize($value);
        foreach ($more as &$arg) {
            $arg = $this->serialize($arg);
        }
        return $this->handler->sAdd($key, $value, ...$more);
    }

    /**
     * set 删除指定元素
     *
     * @param string $name 键名
     * @param mix $value 数据
     * @return mix 失败返回false，成功返回移除元素数量
     */
    public function sRem($name, $value, ...$more)
    {
        $key = $this->getCacheKey($name);
        $value = $this->serialize($value);
        foreach ($more as &$arg) {
            $arg = $this->serialize($arg);
        }
        return $this->handler->sRem($key, $value, ...$more);
    }

    /**
     * set 判断给定值是否集合中的成员
     *
     * @param string $name 键名
     * @param mix $value 数据
     * @return bool 失败返回false，成功返回是否成员bool值
     */
    public function sIsMember($name, $value)
    {
        $key = $this->getCacheKey($name);
        $value = $this->serialize($value);
        return $this->handler->sIsMember($key, $value);
    }

    /**
     * set 获取所有元素
     *
     * @param string $name 键名
     * @return mix 失败返回false，成功返回包含所有元素的数组
     */
    public function sMembers($name)
    {
        $key = $this->getCacheKey($name);
        $data = $this->handler->sMembers($key);
        if (false === $data) {
            return false;
        }
        foreach ($data as &$value) {
            $value = $this->unserialize($value);
        }
        return $data;
    }

    /**
     * set 随机返回一个元素
     *
     * @param string $name 键名
     * @return mix 失败返回false，成功返回元素
     */
    public function sRandMember($name)
    {
        $key = $this->getCacheKey($name);
        $value = $this->handler->sRandMember($key);
        return $this->unserialize($value);
    }

    /**
     * set 随机返回并删除一个元素
     *
     * @param string $name 键名
     * @return mix 失败返回false，成功返回被移除的元素，集合不存在或空集时，返回nil
     */
    public function sPop($name)
    {
        $key = $this->getCacheKey($name);
        $value = $this->handler->sPop($key);
        if (is_null($value)) {
            return null;
        }
        return $this->unserialize($value);
    }

    /**
     * set 将成员从source移动到destination
     *
     * @param string $source 源键名
     * @param string $destination 目标键名
     * @param mix $value 数据
     * @return mix 失败返回false，成功返回1，未进行任何操作返回0
     */
    public function sMove(string $source, string $destination, $value)
    {
        $keySource = $this->getCacheKey($source);
        $keyDestination = $this->getCacheKey($destination);
        $value = $this->serialize($value);
        return $this->handler->sMove($keySource, $keyDestination, $value);
    }

    /**
     * set 获取成员数量
     *
     * @param string $name 键名
     * @return mix 失败返回false，成功返回成员数量，集合不存在时返回0
     */
    public function sCard(string $name)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->sCard($key);
    }

    /**
     * set 获取给定集合的交集
     * @param string $first 键名
     * @param string $second 键名
     * @param string $more 变长参数键名
     * @return mix 失败返回false，成功返回交集数组
     */
    public function sInter(string $first, string $second, ...$more)
    {
        $keyFirst = $this->getCacheKey($first);
        $keySecond = $this->getCacheKey($second);
        foreach ($more as &$arg) {
            $arg = $this->getCacheKey($arg);
        }
        $data = $this->handler->sInter($keyFirst, $keySecond, ...$more);
        if (false === $data) {
            return false;
        }
        foreach ($data as &$value) {
            $value = $this->unserialize($value);
        }
        return $data;
    }

    /**
     * set 获取指定集合的并集
     *
     * @param string $first 键名
     * @param string $second 键名
     * @param string $more 变长参数，键名
     * @return mix 失败返回false，成功返回并集数组
     */
    public function sUnion(string $first, string $second, ...$more)
    {
        $keyFirst = $this->getCacheKey($first);
        $keySecond = $this->getCacheKey($second);
        foreach ($more as &$arg) {
            $arg = $this->getCacheKey($arg);
        }
        $data = $this->handler->sUnion($keyFirst, $keySecond, ...$more);
        if (false === $data) {
            return false;
        }
        foreach ($data as &$value) {
            $value = $this->unserialize($value);
        }
        return $data;
    }

    /**
     * set 获取指定集合的差集
     *
     * @param string $first 键名
     * @param string $second 键名
     * @param string $more 变长参数，键名
     * @return mix 失败返回false，成功返回差集数组
     */
    public function sDiff($first, $second, ...$more)
    {
        $keyFirst = $this->getCacheKey($first);
        $keySecond = $this->getCacheKey($second);
        foreach ($more as &$arg) {
            $arg = $this->getCacheKey($arg);
        }
        $data = $this->handler->sDiff($keyFirst, $keySecond, ...$more);
        if (false === $data) {
            return false;
        }
        foreach ($data as &$value) {
            $value = $this->unserialize($value);
        }
        return $data;
    }

    //set相关操作结束

    //zset相关操作开始

    /**
     * zset 添加元素
     *
     * @param string $name 键名
     * @param integer $score 分值
     * @param mix $value 数据
     * @param mix $more 数据；2个一组，如100,'hello world'
     * @return mix 失败返回false，成功返回添加成功的成员数量
     */
    public function zAdd($name, $score, $value, ...$more)
    {
        $key = $this->getCacheKey($name);
        $value = $this->serialize($value);
        for ($i = 0; $i < count($more); $i += 2) {
            $more[$i + 1] = $this->serialize($more[$i + 1]);
        }
        return $this->handler->zAdd($key, $score, $value, ...$more);
    }

    /**
     * zset 删除指定成员
     *
     * @param string $name 键名
     * @param mix $member 数据
     * @param mix $more 变长参数，数据
     * @return mix 失败返回false，成功返回删除的成员数
     */
    public function zRem($name, $member, ...$more)
    {
        $key = $this->getCacheKey($name);
        $member = $this->serialize($member);
        foreach ($more as &$arg) {
            $arg = $this->serialize($arg);
        }
        return $this->handler->zRem($key, $member, ...$more);
    }
    /**
     * zset 移除指定排名区间所有成员
     *
     * @param string $name 键名
     * @param integer $start 起始排名,从0开始
     * @param integer $stop 结束排名，从0开始，-1表示最后一个
     * @return mix 失败返回false，成功返回移除成员数量
     */
    public function zRemRangeByRank($name, $start, $stop)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->zRemRangeByRank($key, $start, $stop);
    }

    /**
     * zset 移除指定分数区间所有成员
     *
     * @param string $name 键名
     * @param integer $min 最低分值
     * @param integer $max 最高分值
     * @return mix 失败返回false，成功返回移除成员数量
     */
    public function zRemRangeByScore($name, $min, $max)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->zRemRangeByScore($key, $min, $max);
    }

    /**
     * zset 对指定成员的分数加上增量
     *
     * @param string $name 键名
     * @param string $member 成员名
     * @param integer $value 增量值
     * @return mix 失败返回false，成功返回新分数值
     */
    public function zIncrBy($name, $member, $value = 1)
    {
        $key = $this->getCacheKey($name);
        $member = $this->serialize($member);
        return $this->handler->zIncrBy($key, $value, $member);
    }

    /**
     * zset 获取元素数量
     *
     * @param string $name 键名
     * @return mix 失败返回false，成功返回元素数量，键不存在时返回0
     */
    public function zCard($name)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->zCard($key);
    }

    /**
     * zset 获取成员排名，由小到大
     *
     * @param string $name 键名
     * @param string $member 成员
     * @return mix 失败返回false，成功返回下标，成员不存在返回false
     */
    public function zRank($name, $member)
    {
        $key = $this->getCacheKey($name);
        $member = $this->serialize($member);
        return $this->handler->zRank($key, $member);
    }

    /**
     * zset 获取成员排名，由大到小
     *
     * @param string $name 键名
     * @param mix $member 成员
     * @return mix 失败返回false，成功返回下标，成员不存在返回false
     */
    public function zRevRank($name, $member)
    {
        $key = $this->getCacheKey($name);
        $member = $this->serialize($member);
        return $this->handler->zRevRank($key, $member);
    }

    /**
     * zset 获取指定下标区间内成员，从小到大
     *
     * @param string $name
     * @param int $start 起始排名，0开始
     * @param int $end 结束排名，0开始，-1表示最后一个
     * @return mix 失败返回false，成功返回数组，若withScores为true，数组每个元素为['member'=>'m','score'=>100]，若withScores为false，则为元素名
     */
    public function zRange($name, $start, $end, $withScores = false)
    {
        $key = $this->getCacheKey($name);
        $data = $this->handler->zRange($key, $start, $end, $withScores);
        if (false === $data) {
            return false;
        }
        if ($withScores) {
            $result = [];
            //key为成员，scroe为分数
            foreach ($data as $member => $scroe) {
                $result[] = [
                    'member' => $this->unserialize($member),
                    'score' => $scroe,
                ];
            }
            $data = $result;
        } else {
            foreach ($data as &$member) {
                $member = $this->unserialize($member);
            }
        }
        return $data;
    }

    /**
     * zset 获取指定分数区间所有成员，由小到大
     *
     * @param string $name 键名
     * @param integer $min 最小分数
     * @param integer $max 最大分数
     * @param integer $withScores 是否获取分数
     * @param integer $offset 偏移量
     * @param integer $count 获取数量
     * @return mix 失败返回false，成功返回数组，若withScores为true，数组每个元素为['member'=>'m','score'=>100]，若withScores为false，则为元素名
     */
    public function zRangeByScore($name, $min, $max, $withScores = false, $offset = 0, $count = -1)
    {
        $key = $this->getCacheKey($name);
        $data = $this->handler->zRangeByScore($key, $min, $max, [
            'withscores' => $withScores,
            'limit' => [$offset, $count],
        ]);
        if (false === $data) {
            return false;
        }
        if ($withScores) {
            $result = [];
            //key为成员，scroe为分数
            foreach ($data as $member => $scroe) {
                $result[] = [
                    'member' => $this->unserialize($member),
                    'score' => $scroe,
                ];
            }
            $data = $result;
        } else {
            foreach ($data as &$member) {
                $member = $this->unserialize($member);
            }
        }
        return $data;
    }

    /**
     * zset 获取指定下标区间内成员，从大到小
     *
     * @param string $name
     * @param int $start 起始排名，0开始
     * @param int $end 结束排名，0开始，-1表示最后一个
     * @return mix 失败返回false，成功返回数组，若withScores为true，数组每个元素为['member'=>'m','score'=>100]，若withScores为false，则为元素名
     */
    public function zRevRange($name, $start, $end, $withScores = false)
    {
        $key = $this->getCacheKey($name);
        $data = $this->handler->zRevRange($key, $start, $end, $withScores);
        if (false === $data) {
            return false;
        }
        if ($withScores) {
            $result = [];
            //key为成员，scroe为分数
            foreach ($data as $member => $scroe) {
                $result[] = [
                    'member' => $this->unserialize($member),
                    'score' => $scroe,
                ];
            }
            $data = $result;
        } else {
            foreach ($data as &$member) {
                $member = $this->unserialize($member);
            }
        }
        return $data;
    }

    /**
     * zset 获取指定分数区间所有成员，由大到小
     *
     * @param string $name 键名
     * @param integer $max 最大分数
     * @param integer $min 最小分数
     * @param integer $withScores 是否获取分数
     * @param integer $offset 偏移量
     * @param integer $count 获取数量
     * @return mix 失败返回false，成功返回数组，若withScores为true，数组每个元素为['member'=>'m','score'=>100]，若withScores为false，则为元素名
     */
    public function zRevRangeByScore($name, $max, $min, $withScores = false, $offset = 0, $count = -1)
    {
        $key = $this->getCacheKey($name);
        $data = $this->handler->zRevRangeByScore($key, $max, $min, [
            'withscores' => $withScores,
            'limit' => [$offset, $count],
        ]);
        if (false === $data) {
            return false;
        }
        if ($withScores) {
            $result = [];
            //key为成员，scroe为分数
            foreach ($data as $member => $scroe) {
                $result[] = [
                    'member' => $this->unserialize($member),
                    'score' => $scroe,
                ];
            }
            $data = $result;
        } else {
            foreach ($data as &$member) {
                $member = $this->unserialize($member);
            }
        }
        return $data;
    }

    /**
     * zset 获取成员分数值
     *
     * @param string $name 键名
     * @param mix $member 成员
     * @return mix 失败返回false，成功返回分数值
     */
    public function zScore($name, $member)
    {
        $key = $this->getCacheKey($name);
        $member = $this->serialize($member);
        return $this->handler->zScore($key, $member);
    }

    /**
     * zset 获取指定分数区间的成员数量
     *
     * @param string $name 键名
     * @param integer $min 最小分数
     * @param integer $max 最大分数
     * @return mix 失败返回false，成功返回成员数量
     */
    public function zCount($name, $min, $max)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->zCount($key, $min, $max);
    }

    //zset相关操作结束

}
