<?php

namespace alocms\model;

use PDO;
use think\db\Query;
use think\helper\Str;
use think\Model;

/**
 * Model数据基类
 */
class Base extends Model
{
    /**
     * 主键
     *
     * @var string
     */
    protected $pk = 'id';

    /**
     * 字段前缀
     *
     * @var string
     */
    protected $prefix = '';

    public function __construct()
    {
        // 处理表名前缀
        $prefix = $this->db()->getConfig('prefix');
        if (!\is_null($prefix)) {
            $this->table = \str_replace('{$database_prefix}_', $prefix, $this->table);
        }
        parent::__construct();
    }

    /**
     * 获取字段前缀
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * 获取字段前缀
     *
     * @return string
     */
    public function getPk(): string
    {
        return $this->pk;
    }

    /**
     * 额外的函数处理
     *
     * @param string $method 方法名
     * @param array $args 参数
     * @return void
     */
    public function __call($method, $args)
    {
        // 处理cond打头的不存在函数，然后转换成通用的键值数组返回
        $pos = \strpos($method, 'cond');
        if (0 === $pos) {
            $field = Str::snake(\substr($method, 4));
            return [
                "{$this->prefix}{$field}" => $args[0],
            ];
        }
        return parent::__call($method, $args);
    }

    /**
     * 设置应用条件
     *
     * @param integer $appType 应用类型
     * @return Query
     */
    public function baseAppTypeQuery(int $appType): Query
    {
        $condition = $this->condAppType($appType);
        return $this->where($condition);
    }

    /**
     * 获取当前类的实例
     *
     * @param bool $newInstance 是否新实例，默认true
     * @param array $args 构造函数所需参数
     * @return static
     */
    public static function instance(bool $newInstance = true, array $args = [])
    {
        return app(static::class, $args, $newInstance);
    }
}
