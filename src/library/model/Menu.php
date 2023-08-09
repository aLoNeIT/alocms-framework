<?php

declare(strict_types=1);

namespace alocms\model;

use think\db\Query;
use think\model\relation\HasMany;
use think\model\relation\HasOne;

class Menu extends Base
{
    /** @inheritDoc */
    protected $table = '{$database_prefix}_menu';
    /** @inheritDoc */
    protected $pk = 'mn_id';
    /** @inheritDoc */
    protected $prefix = 'mn_';
    /**
     * 关联菜单的功能
     *
     * @return HasMany
     */
    public function functions(): HasMany
    {
        return $this->hasMany(FunctionModel::class, 'fn_menu_code', 'mn_code');
    }
    /**
     * 关联菜单的页面
     *
     * @return HasOne
     */
    public function page(): HasOne
    {
        return $this->hasOne(Page::class, 'p_id', 'mn_page');
    }

    /**
     * 根据父级菜单获取数据
     *
     * @param string|null $parent 父级菜单编码
     * @param integer $appType 应用类型
     * @return Query
     */
    public function getByParent(?string $parent = null, int $appType = 1): Query
    {
        $condition = $this->condAppType($appType);
        $query = $this->where($condition);
        $query = \is_null($parent) ? $query : $query->where('mn_parent_code', $parent);
        return $query;
    }
    /**
     * 根据uri获取菜单数据
     *
     * @param string $uri uri地址
     * @param integer $appType 应用类型
     * @return Query
     */
    public function getByUri(string $uri, int $appType = 3): Query
    {
        $condition = $this->condAppType($appType);
        $query = $this->where($condition)->where('mn_uri', $uri);
        return $query;
    }
}
