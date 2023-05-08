<?php

namespace alocms\library\util;

use alocms\library\util\Dict;
use alocms\library\util\Helper;

/**
 * 字典项
 *
 * @author alone <alone@alonetech.com>
 */
class DictItem
{
    /**
     * 保存字典项原始数据
     *
     * @var array
     */
    protected $data = [];

    /**
     * 保存的字典对象
     *
     * @var Dict
     */
    protected $dict = null;

    /**
     * 构造函数
     *
     * @param Dict $dict 字典对象
     * @param array $data 字典项原始数据
     */
    public function __construct(Dict $dict, array $data = [])
    {
        $this->dict = $dict;
        $this->data = $data;
    }

    public function __get($name)
    {
        if (isset($this->data['di_' . strtolower($name)])) {
            return $this->data['di_' . strtolower($name)];
        }
    }

    public function __set($name, $value)
    {
        if (isset($this->data['di_' . strtolower($name)])) {
            $this->data['di_' . strtolower($name)] = $value;
        }
    }

    /**
     * 载入数据字典
     * 
     * @return static 返回当前对象
     */
    public function load($data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 返回字典原始数据
     *
     * @param bool $prefix 是否保留前缀
     * @return array 返回字典项数据，数组kv形式
     */
    public function toArray(bool $prefix = true): array
    {
        if (false == $prefix) {
            $data = [];
            foreach ($this->data as $key => $value) {
                $data[Helper::delPrefix($key, 'di_')] = $value;
            }
            // 额外处理field_name数据
            $data['fieldname'] = Helper::delPrefix($data['fieldname'], $this->dict->prefix);
            if ($this->key_dict > 0) {
                $data['key_field'] = $this->replacePrefix($data['key_field'], \strtolower(\parse_name($data['key_join_name'] ?: $data['key_table'])));
                $data['key_show'] = $this->replacePrefix($data['key_show'], \strtolower(\parse_name($data['key_join_name'] ?: $data['key_table'])));
            }
            if ($this->link_dict > 0) {
                $data['link_field'] = $this->replacePrefix($data['link_field'], \strtolower(\parse_name($data['link_table'])));
            }
            if ($this->show_dict > 0) {
                $data['show_field'] = $this->replacePrefix($data['show_field'], \strtolower(\parse_name($data['show_table'])));
            }
            return $data;
        }
        return $this->data;
    }
    /**
     * 替换前缀
     *
     * @param string $key 键名
     * @param string $newPrefix 新前缀
     * @return string 返回处理后的前缀
     */
    protected function replacePrefix(string $key, string $newPrefix = ''): string
    {
        $arr = \explode('_', $key);
        array_shift($arr);
        array_unshift($arr, $newPrefix);
        return \join('_', $arr);
    }

    /**
     * 清除保存的内容
     */
    public function clear(): void
    {
        $this->data = [];
    }
    /**
     * 获取字典项原始配置数据
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
    /**
     * 批量设置字典项数据
     *
     * @param array $data 字典项数据结构
     * @param bool $prefixed 是否带有前缀
     * @return static 返回当前字典项对象
     */
    public function setData(array $data, bool $prefixed = false): static
    {
        foreach ($data as $key => $value) {
            $prefix = $prefixed ? '' : 'di_';
            if (isset($this->data["{$prefix}{$key}"])) {
                $this->data["{$prefix}{$key}"] = $value;
            }
        }
        return $this;
    }
}
