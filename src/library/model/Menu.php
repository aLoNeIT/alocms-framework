<?php

declare(strict_types=1);

namespace alocms\model;

use think\db\Query;

class Menu extends Base
{
    /** @inheritDoc */
    protected $table = '{$database_prefix}_menu';
    /** @inheritDoc */
    protected $pk = 'mn_id';
    /** @inheritDoc */
    protected $prefix = 'mn_';

    /**
     * 根据父级菜单获取数据
     *
     * @param integer $appType 应用类型
     * @param string|null $parent 父级菜单编码
     * @return Query
     */
    public function getDataByParent(int $appType = 1, ?string $parent = null): Query
    {
        $condition = $this->condAppType($appType);
        $query = $this->where($condition);
        $query = \is_null($parent) ? $query : $query->where('mn_parent_code', $parent);
        return $query;
    }
}
