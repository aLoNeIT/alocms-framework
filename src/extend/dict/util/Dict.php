<?php

declare(strict_types=1);

namespace alocms\extend\dict\util;

use alocms\extend\dict\util\DictItem;
use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\util\Helper;

/**
 * 字典类，对应一个数据库表
 * 
 * @property integer $id 字典id
 * @property string $name 字典名称
 * @property string $tablename 字典对应的表名
 * @property string $sub 字典对应的子表名
 * @property string $prefix 字典对应的表前缀
 * 
 * @author alone <alone@alonetech.com>
 */
class Dict
{
    /**
     * 字典内包含的属性
     */
    const DICT_PROPERTIES = ['id', 'name', 'tablename', 'sub', 'prefix'];

    /**
     * 字典内的原始数据
     *
     * @var array
     */
    protected $data = [];
    /**
     * 字典项数据，元素都是DictItem对象
     *
     * @var array
     */
    protected $items = [];
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
    public function __construct(array $data = [], array $items = [])
    {
        $this->load($data);
        $this->loadItems($items);
    }
    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->clear();
    }
    /**
     * 获取新的字典对象
     *
     * @param array|null $fieldNames 指定的字典项fieldname数组
     * @return static 返回新的字典对象
     */
    public function newInstance(?array $fieldNames = null): static
    {
        // 如果不要求指定字段，则直接返回当前对象的克隆
        if (\is_null($fieldNames)) {
            return clone $this;
        }
        // 创建新的字典对象
        $dictData = $this->data;
        $dictItemData = [];
        // 获取指定对象的字段值
        foreach ($fieldNames as $fieldName) {
            $dictItem = $this->getItem($fieldName);
            if (!\is_null($dictItem)) {
                // 查询到有效子项，则将其添加到新的字典对象中
                $dictItemData[] = $dictItem->getData();
            }
        }
        return new static($dictData, $dictItemData);
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
        // 参数校验
        foreach (self::DICT_PROPERTIES as $property) {
            if (!isset($data[$property])) {
                Helper::exception(
                    ErrCodeFacade::getJError(100, [
                        'property' => $property
                    ])
                );
            }
            $this->data[$property] = $data[$property];
        }
        return $this;
    }

    /**
     * 批量载入字典项数据
     *
     * @param array $data 字典项数据，每个元素是一个字典项对象或者字典项数据
     * @return static 返回当前对象
     */
    public function loadItems(array $data): static
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
     * @return DictItem 返回字典项对象
     */
    public function addItem($data): DictItem
    {
        /** @var DictItem $item */
        $item = null;
        if ($data instanceof DictItem) {
            $item = $data;
        } else {
            $item = new DictItem($this, $data);
        }
        $this->items[$item->fieldname] = $item;
        return $item;
    }

    /**
     * 删除子项
     *
     * @param DictItem|string $fieldName 字典项名称或者字典项对象
     * @return static 返回当前对象
     */
    public function removeItem($fieldName): static
    {
        if ($fieldName instanceof DictItem) {
            $fieldName = $fieldName->fieldname;
        }
        if ($this->exists($fieldName)) {
            unset($this->items[$fieldName]);
        }
        return $this;
    }

    /**
     * 获取指定的字典项
     *
     * @param string $fieldName 获取指定名称的字典项
     * @return DictItem|null 返回获取到的字典项对象，如果不存在返回null
     */
    public function getItem(string $fieldName): ?DictItem
    {
        return $this->item[$fieldName] ?? null;
    }
    /**
     * 判断是否存在指定字典项
     *
     * @param string $fieldName 字典项名称
     * @return boolean 返回是否存在字典项
     */
    public function exists(string $fieldName): bool
    {
        return isset($this->item[$fieldName]);
    }
    /**
     * 获取所有字典项
     *
     * @param bool $prefixed 返回数组是否需要前缀
     * @return array 返回所有字典项，每个元素都是DictItem对象
     */
    public function getItems(): array
    {
        return $this->item;
    }
    /**
     * 将同样的字典项数据设置到多个字典项中
     *
     * @param array $value 字典项数据
     * @param array|null $fieldNames 字典项名称数组，[fieldName1,fieldName2]
     * @return static 返回当前对象
     */
    public function setItemsValue(array $value, ?array $fieldNames = null): static
    {
        if (empty($fieldNames)) {
            Helper::exception(
                ErrCodeFacade::getJError(101)
            );
        }
        $this->eachItem(function (string $key, DictItem $item) use ($value) {
            $item->setData($value);
        }, $fieldNames);
        return $this;
    }
    /**
     * 对每一个字典项应用回调
     *
     * @param callable $callback 回调函数，回调参数为：string $key,DictItem $item
     * @return boolean 返回执行结果
     */
    public function eachItem(callable $callback, ?array $fieldNames = null): bool
    {
        // 循环所有字典项
        foreach ($this->items as $key => $item) {
            // 传递了有效的字典项名称数组，但是当前字典项不在数组中，跳过
            if (!\is_null($fieldNames) && !empty($fieldNames) && !\in_array($key, $fieldNames)) {
                continue;
            }
            // 传递了有效的字典项名称数组，则只对指定的字典项执行回调
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
        $this->items = [];
    }

    /**
     * 获取字典项数量
     *
     * @return integer
     */
    public function itemCount(): int
    {
        return count($this->items);
    }
    /**
     * 魔术方法，主要用于快速获取字典项值，无需输入d_前缀
     *
     * @param string $name 属性名
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->data[strtolower($name)])) {
            return $this->data[strtolower($name)];
        }
        Helper::exception(
            ErrCodeFacade::getJError(100, [
                'property' => $name
            ])
        );
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
            foreach ($this->items as $key => $item) {
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
     * @param bool $prefixed 值是否带前缀
     * @return array 返回数组形式的字典数据
     */
    public function toArray(bool $prefixed = false): array
    {
        $dictItem = [];
        $this->eachItem(function (string $key, DictItem $item) use (&$dictItem, $prefixed) {
            $dictItem[$key] = $item->toArray($prefixed);
        });
        return array_merge($this->items, [
            'dict_item' => $dictItem,
        ]);
    }
    /**
     * clone魔术方法
     *
     * @return void
     */
    public function __clone()
    {
        $items = [];
        // 创建字典项新对象
        foreach ($this->items as $item) {
            $items[$item->fieldname] = new DictItem($this, $item->getData());
        }
        $this->items = $items;
        $this->primaryKey = null;
    }
}
