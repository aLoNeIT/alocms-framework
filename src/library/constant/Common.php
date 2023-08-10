<?php

declare(strict_types=1);

namespace alocms\constant;

class Common
{
    /**
     * APP_TYPE*  是对应用类型的配置
     */
    /**
     * 应用类型，通用
     */
    const APP_TYPE_COMMON = 0;
    /**
     * 应用类型，后台
     */
    const APP_TYPE_ADMIN = 1;
    /**
     * 应用类型，集团
     */
    const APP_TYPE_CORPORATION = 2;
    /**
     * 应用类型，机构
     */
    const APP_TYPE_ORGANIZATION = 3;
    /**
     * 应用类型，用户
     */
    const APP_TYPE_USER = 4;
    /**
     * 应用类型映射名称
     */
    const APP_TYPE_MAP = [
        self::APP_TYPE_ADMIN => 'admin',
        self::APP_TYPE_CORPORATION => 'corporation',
        self::APP_TYPE_ORGANIZATION => 'organization',
        self::APP_TYPE_USER => 'user',
    ];
}
