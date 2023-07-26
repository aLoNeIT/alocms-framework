<?php

declare(strict_types=1);

namespace alocms\logic;

use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\model\Dict as DictModel;
use alocms\model\DictItem as DictItemModel;
use alocms\util\CacheConst;
use alocms\util\CmsException;
use alocms\util\Helper;
use alocms\util\JsonTable;
use dict\facade\Dict as DictFacade;
use dict\interface\Processor as DictProcessorInterface;
use dict\util\Dict as DictUtil;
use dict\util\DictItem as DictItemUtil;
use think\db\exception\PDOException;
use think\Model;

/**
 * 字典处理类
 *
 * @author 王阮强 <wangruanqiang@youzhibo.cn>
 */
class Dict extends Base implements DictProcessorInterface
{
    /**
     * 缓存的字典数据，里面都是Dict对象
     *
     * @var array
     */
    protected $item = [];

    /**
     * 索引是表名的字典数据
     *
     * @var array
     */
    protected $itemName = [];

    /** @inheritDoc */
    public function getDict(int $id, int $appType = 0, bool $newInstance = false): DictUtil
    {
        $dictUtil = null;
        $key = CacheConst::dict($id, $appType);
        if (!isset($this->item[$key])) {
            // 内存中不存在字典数据
            if (!cache("?{$key}")) {
                // 缓存中也不存在，则读取数据库
                $dictModel = DictModel::find($id);
                if (\is_null($dictModel)) {
                    // 未查询到有效数据
                    throw new CmsException(
                        ErrCodeFacade::getJError(
                            40,
                            [
                                'id' => $id,
                            ]
                        )
                    );
                }
                // 查询dictitem数据
                /** @var \think\model\Collection $dictItemModel */
                $dictItemModel = DictItemModel::where('di_dict', $id)->where('di_app_type', 'in', [0, $appType])
                    ->order('di_show_order asc  ,di_id asc')->select();
                if ($dictItemModel->isEmpty()) {
                    // 未查询到有效数据
                    throw new CmsException(
                        ErrCodeFacade::getJError(
                            41,
                            [
                                'id' => $id,
                                'name' => 'all',
                            ]
                        )
                    );
                }
                // 读取到的数据做处理
                $dictData = Helper::delPrefixArr($dictModel->toArray(), 'd_');
                $dictItemData = Helper::delPrefixArr($dictItemModel->toArray(), 'di_');
                $dictUtil = new DictUtil($dictData, $dictItemData);
                cache("{$key}", $dictUtil, CacheConst::ONE_DAY);
            } else {
                $dictUtil = cache("{$key}");
            }
            $this->item[$key] = $dictUtil;
        } else {
            $dictUtil = $this->item[$key];
        }
        // 如果需要返回克隆对象，则通过序列化做一次深拷贝
        return $newInstance ? \unserialize(\serialize($dictUtil)) : $dictUtil;
    }

    /** @inheritDoc */
    public function getModel(DictUtil $dict, string $module = 'common'): Model
    {
        $modelName = $dict->tablename;
        // 拼凑类完整命名 确认文件是否存在
        return Helper::model($modelName, false, $module);
    }

    /** @inheritDoc */
    public function getTablePrefix(): string
    {
        return \env('database.prefix', '');
    }

    /**
     * 根据表名获取字典数据
     *
     * @param string  $tableName 表名
     * @param integer $appType 应用类型
     *
     * @return DictUtil 返回获取到的字典对象
     */
    public function getDictByTableName(string $tableName, int $appType = 0): DictUtil
    {
        $key = CacheConst::dictTableName($tableName, $appType);
        if (isset($this->itemName[$key])) {
            return $this->itemName[$key];
        }
        // 未查询到数据，则到数据库查询对应id
        $dictId = DictModel::where('d_tablename', $tableName)->value('d_id');
        if (\is_null($dictId)) {
            throw new CmsException(
                ErrCodeFacade::getJError(
                    42,
                    []
                )
            );
        }
        $dict = $this->getDict($dictId, $appType);
        $this->itemName[$key] = $dict;
        return $dict;
    }

    /**
     * 通过模型获取表名称
     *
     * @param Model $model 模型对象
     *
     * @return string 表名称
     */
    public function getNameByModel(Model $model): string
    {
        $dict = $this->getDictByTableName($model->getName());
        return $dict->name;
    }

    /**
     * 获取字典项
     *
     * @param integer $id 字典id
     * @param string  $fieldName 字典项字段名
     * @param integer $appType  应用类型
     * @return DictItem
     * @throws CmsException 字典项不存在
     */
    public function getDictItem(int $id, string $fieldName, int $appType = 0): DictItemUtil
    {
        $dict = $this->getDict($id, $appType);
        $fieldName = Helper::delPrefix($fieldName, $dict->prefix);
        $dictItem = $dict->getItem($fieldName);
        if (\is_null($dictItem)) {
            // 获取不到有效的字典项数据，抛出异常
            throw new CmsException(
                ErrCodeFacade::getJError(
                    41,
                    [
                        'id' => $id,
                        'name' => $fieldName,
                    ]
                )
            );
        }
        return $dictItem;
    }

    /**
     * 获取字典项数量
     *
     * @param integer $id 字典id
     * @param integer $appType 应用类型
     * @return integer 返回字典项数量
     */
    protected function getItemCount($id, int $appType = 0): int
    {
        $dict = $this->getDict($id, $appType);
        return $dict->itemCount();
    }

    /**
     * 查询数据
     *
     * @param integer|DictUtil $dict 字典id或字典对象
     * @param array $condition 表达式
     * @param array|null $order 排序，若非null则覆盖字典的排序配置
     * @param string  $fuzzy 模糊查询内容
     * @param integer $currPage 查询的页码
     * @param integer $pageNum 每页数据量，为0则代表不分页
     * @param integer $appType 应用类型
     * @return JsonTable 返回JsonTable对象，msg节点是分页数据，data节点是查询结果
     */
    public function select(
        int|DictUtil $dict,
        array $condition = [],
        ?array $order = null,
        string $fuzzy = null,
        int $currPage = 1,
        int $pageNum = 20,
        int $appType = 0
    ): JsonTable {
        try {
            if (!($dict instanceof DictUtil)) {
                $dict = $this->getDict($dict);
            }
            $query = DictFacade::build($dict, 1, $condition, $order, $fuzzy, $appType);
            // 该条件的总数据量
            $totalCount = $query->count();
            $totalPage = 1; // 默认只有一页
            // 查询指定数据
            if (0 !== $pageNum) {
                // 分页查匹配的数据
                $totalPage = \max(ceil($totalCount / $pageNum), $totalPage); // 向上取整获取总页码
                $currPage = min($currPage, $totalPage);
                $query = $query->limit(($currPage - 1) * $pageNum, $pageNum);
            }
            $data = $query->select();
            // 返回查询结果
            return $this->jsonTable->success(
                [
                    'curr' => $currPage,
                    'page' => $totalPage,
                    'num' => $pageNum,
                    'count' => $totalCount,
                ],
                $data->toArray()
            );
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 通过主键查询数据
     *
     * @param integer|DictUtil $dict 字典id或字典对象
     * @param string|integer $id 主键值
     * @param array|null $order 排序，若非null则覆盖字典的排序配置
     * @param integer $appType 应用类型
     * @return JsonTable 返回JsonTable对象，data节点是查询到的数据
     */
    public function findByPrimaryKey(int|DictUtil $dict, string|int $id, ?array $order = null, int $appType = 0): JsonTable
    {
        if (!($dict instanceof DictUtil)) {
            $dict = $this->getDict($dict);
        }
        try {
            if (!($dict instanceof DictUtil)) {
                $dict = $this->getDict($dict);
            }
            $query = DictFacade::findByPrimaryKey($dict, $id, $order, $appType);
            $data = $query->find();
            if (\is_null($data)) {
                return ErrCodeFacade::getJError(25, ['name' => $dict->name]);
            }
            return $this->jsonTable->successByData($data->toArray());
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 查询单条数据
     *
     * @param integer|Dict $dict 字典id或字典对象
     * @param array $condition 条件表达式
     * @param array|null $order 排序，若非null则覆盖字典的排序配置
     * @param integer $appType 应用类型
     * @return JsonTable 返回JsonTable对象，data节点是查询到的数据
     */
    public function find($dict, array $condition = [], ?array $order = null, int $appType = 0): JsonTable
    {
        try {
            if (!($dict instanceof DictUtil)) {
                $dict = $this->getDict($dict);
            }
            $query = DictFacade::find($dict, 8, $condition, $order, null, $appType);
            $data = $query->find();
            if (\is_null($data)) {
                return ErrCodeFacade::getJError(25, ['name' => $dict->name]);
            }
            return $this->jsonTable->successByData($data->toArray());
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 更新数据
     *
     * @param integer|Dict $dict 字典id或字典对象
     * @param array $data 数据
     * @param array $condition 条件表达式
     * @param integer $appType 应用类型
     * @return JsonTable 返回JsonTable对象，data节点是更新后的数据
     */
    public function update(int|DictUtil $dict, array $data, array $condition = [], int $appType = 0): JsonTable
    {
        try {
            if (!($dict instanceof DictUtil)) {
                $dict = $this->getDict($dict);
            }
            // 添加数据前缀
            $data = Helper::addPrefixArr($data, $dict->prefix);
            // 数据校验
            $result = DictFacade::checkData($dict, 4, $data, false, $appType);
            if (true !== $result) {
                return \is_string($result) ? $this->jsonTable->error($result) : $this->jsonTable->error('error', 1, $result);
            }
            // 获取处理后的query对象
            $query = DictFacade::build($dict, 4, $condition, null, null, $appType);
            $model = $query->find();
            if (\is_null($model)) {
                return ErrCodeFacade::getJError(25, ['name' => $dict->name]);
            }
            $model->save($data);
            return $this->jsonTable->successByData(Helper::delPrefixArr($model->toArray(), $dict->prefix));
        } catch (PDOException $ex) {
            Helper::logListenCritical(static::class, __FUNCTION__, $ex);
            return ErrCodeFacade::getJError(21);
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 创建新数据
     *
     * @param integer|Dict $dict 字典id或字典对象
     * @param array $data 新建的数据
     * @param integer $appType 应用类型
     *
     * @return JsonTable 返回JsonTable对象，msg节点是新建的数据主键值，data节点是新建的数据
     */
    public function save($dict, array $data = [], int $appType = 0): JsonTable
    {
        try {
            if (!($dict instanceof DictUtil)) {
                $dict = $this->getDict($dict);
            }
            // 添加数据前缀
            $data = Helper::addPrefixArr($data, $dict->prefix);
            // 数据校验
            $result = DictFacade::checkData($dict, 4, $data, false, $appType);
            if (true !== $result) {
                return \is_string($result) ? $this->jsonTable->error($result) : $this->jsonTable->error('error', 1, $result);
            }
            $query = DictFacade::build($dict, 2, [], null, null, $appType);
            // 使用模型写入
            $model = $query->getModel();
            $model->save($data);
            return $this->jsonTable->success($model->getKey(), Helper::delPrefixArr($model->toArray(), $dict->prefix));
        } catch (PDOException $ex) {
            Helper::logListenCritical(static::class, __FUNCTION__, $ex);
            return ErrCodeFacade::getJError(21);
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 删除数据
     *
     * @param integer|Dict $dict 字典id或字典对象
     * @param array $condition 表达式
     * @param integer $appType 应用类型
     * @return JsonTable 返回JsonTable对象
     */
    public function delete($dict, array $condition = [], int $appType = 0): JsonTable
    {
        try {
            if (!($dict instanceof DictUtil)) {
                $dict = $this->getDict($dict);
            }
            $query = DictFacade::build($dict, 16, $condition, null, null, $appType);
            $model = $query->find();
            if (\is_null($model)) {
                return ErrCodeFacade::getJError(25, ['name' => $dict->name]);
            }
            return $model->delete() ? $this->jsonTable->success() : ErrCodeFacade::getJError(23);
        } catch (PDOException $ex) {
            Helper::logListenCritical(static::class, __FUNCTION__, $ex);
            return ErrCodeFacade::getJError(21);
        } catch (\Exception $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }
}
