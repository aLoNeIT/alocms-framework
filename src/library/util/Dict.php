<?php

declare(strict_types=1);

namespace alocms\util;

use alocms\util\DictItem;
use alocms\util\Helper;

/**
 * 字典类
 * 
 * @property string $name 字典名称
 */
class Dict
{
    /**
     * 字典数据
     *
     * @var array
     */
    protected $data = [];
    /**
     * 字典项数据，元素都是DictItem对象
     *
     * @var array
     */
    protected $item = [];
    /**
     * 主键字典项
     *
     * @var DictItem
     */
    protected $primaryKey = null;

    /**
     * 字典构造函数
     *
     * @param array $data 初始化的字典主数据，后期也可以通过load方法载入
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->clear();
    }

    /**
     * 载入新的字典数据
     *
     * @param array $data 字典数据
     * @return static 返回当前对象
     */
    public function load(array $data): static
    {
        $this->clear();
        $this->data = $data;
        return $this;
    }

    /**
     * 批量载入字典项数据
     *
     * @param array $data 字典项数据，每个元素是一个字典项对象或者字典项数据
     * @return static 返回当前对象
     */
    public function loadItem(array $data): static
    {
        foreach ($data as $index => $item) {
            $this->addItem($item);
        }
        return $this;
    }

    /**
     * 添加一个字典项
     *
     * @param array|DictItem $data 字典项数据
     * @return static 返回当前对象
     */
    public function addItem($data): static
    {
        if ($data instanceof DictItem) {
            $this->item[$data->fieldname] = $data;
        } else {
            $item = new DictItem($this, $data);
            $this->item[$item->fieldname] = $item;
        }
        return $this;
    }

    /**
     * 获取指定的字典项
     *
     * @param string $fieldName 获取指定名称的字典项
     * @param bool $prefixed 传递的字段名是否再有前缀，函数内自动补全前缀
     * @return DictItem|null 返回获取到的字典项对象，如果不存在返回null
     */
    public function getItem(string $fieldName, bool $prefixed = false): ?DictItem
    {
        $fieldName = $prefixed ? $fieldName : "{$this->prefix}{$fieldName}";
        return $this->item[$fieldName] ?? null;
    }
    /**
     * 判断是否存在指定字典项
     *
     * @param string $fieldName 字典项名称
     * @param bool $prefixed 传递的字段名是否再有前缀，函数内自动补全前缀
     * @return boolean 返回是否存在字典项
     */
    public function exists(string $fieldName, bool $prefixed = false): bool
    {
        $fieldName = $prefixed ? $fieldName : "{$this->prefix}{$fieldName}";
        return isset($this->item[$fieldName]);
    }
    /**
     * 获取所有字典项
     *
     * @param bool $prefixed 返回数组是否需要前缀
     * @return array 返回所有字典项
     */
    public function getItemAll(bool $prefixed = false): array
    {
        return $prefixed ? $this->item : Helper::delPrefixArr($this->item, $this->prefix);
    }
    /**
     * 设置所有字典项数据
     *
     * @param array $data 待设置的数据，二级数组，['key'=>['fieldname'=>'value']]
     * @param bool $prefixed key是否带有前缀，默认无前缀
     * @return static 返回当前对象，链式操作
     */
    public function setItemAll(array $data, bool $prefixed = false): static
    {
        foreach ($data as $key => $value) {
            $fieldName = $prefixed ? $key : "{$this->prefix}{$key}";
            if (isset($this->item[$fieldName])) {
                $this->item[$fieldName]->setData($value);
            }
        }
        return $this;
    }
    /**
     * 将同样的字典项数据设置到多个字典项中
     *
     * @param array $value 字典项数据
     * @param array $fieldNames 字典项名称数组，[fieldName1,fieldName2]
     * @param bool $prefixed key是否带有前缀，默认无前缀
     * @return static 返回当前对象
     */
    public function setItemAllByValue(array $value, array $fieldNames = [], bool $prefixed = false): static
    {
        $fieldNames = empty($fieldNames) ? \array_keys($this->item) : $fieldNames;
        foreach ($fieldNames as $fieldName) {
            $fieldName = $prefixed ? $fieldName : "{$this->prefix}{$fieldName}";
            if (isset($this->item[$fieldName])) {
                $this->item[$fieldName]->setData($value, $prefixed);
            }
        }
        return $this;
    }
    /**
     * 对每一个字典项应用回调
     *
     * @param callable $callback 回调函数
     * @return boolean 返回执行结果
     */
    public function eachItem(callable $callback): bool
    {
        foreach ($this->item as $key => $item) {
            $result = call_user_func($callback, $key, $item);
            if (false === $result) {
                return false;
            }
        }
        return true;
    }

    /**
     * 清理数据
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->data = [];
        $this->item = [];
    }

    /**
     * 获取字典项数量
     *
     * @return integer
     */
    public function itemCount(): int
    {
        return count($this->item);
    }
    /**
     * 魔术方法，主要用于快速获取字典项值，无需输入d_前缀
     *
     * @param string $name 属性名
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->data['d_' . strtolower($name)])) {
            return $this->data['d_' . strtolower($name)];
        }
    }

    /**
     * 获取主键字典项
     *
     * @return DictItem|null 返回主键字典项，不存在则返回null
     */
    public function getPrimaryKey(): ?DictItem
    {
        if ($this->primaryKey) {
            return $this->primaryKey;
        } else {
            foreach ($this->item as $key => $item) {
                if (1 === $item->pk) {
                    $this->primaryKey = $item;
                    return $this->primaryKey;
                }
            }
        }
        return null;
    }

    /**
     * 以数组形式返回字典数据
     *
     * @param bool $prefixed 是否key带前缀
     * @return array 返回数组形式的字典数据
     */
    public function toArray(bool $prefixed = true): array
    {
        $data = [];
        foreach ($this->data as $key => $value) {
            $data[$prefixed ? $key : Helper::delPrefix($key, 'd_')] = $value;
        }
        $dictItem = [];
        // 循环处理，如果key不带前缀，则删除key前缀
        foreach ($this->item as $key => $value) {
            $dictItem[$prefixed ? $key : Helper::delPrefix($key, $this->prefix)] = $value->toArray($prefixed);
        }
        return array_merge($data, [
            'dict_item' => $dictItem,
        ]);
    }
}
