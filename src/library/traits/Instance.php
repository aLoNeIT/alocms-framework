<?php

declare(strict_types=1);

namespace alocms\traits;

/**
 * 单例模式Trait
 */
trait Instance
{
    /**
     * 获取当前类的实例
     *
     * @param bool $newInstance 是否新实例，默认true
     * @param array $args 构造函数所需参数
     * @return static
     */
    public static function instance(bool $newInstance = false, array $args = []): static
    {
        return \app(static::class, $args, $newInstance);
    }
}
