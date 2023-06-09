<?php

declare(strict_types=1);

namespace alocms\logic;

use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\model\Base as BaseModel;
use alocms\model\Dict as DictModel;
use alocms\model\DictItem as DictItemModel;
use alocms\util\CacheConst;
use alocms\util\CmsException;
use alocms\util\Dict as DictUtil;
use alocms\util\DictItem as DictItemUtil;
use alocms\util\Helper;
use alocms\util\JsonTable;
use think\db\exception\PDOException;
use think\db\Query;
use think\helper\Str;
use think\Model;

/**
 * 字典处理类
 *
 * @author 王阮强 <wangruanqiang@youzhibo.cn>
 * @date   2020-11-02
 */
class Dict extends Base
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

    /**
     * 获取指定字典对象
     *
     * @param integer|Dict $dict 字典id或者字典对象
     * @param integer $appType 应用类型
     * @param boolean $cloned 是否需要返回克隆对象
     * @return DictUtil 返回字典对象
     * @throws CmsException
     */
    public function getDict($dict, int $appType = 0, bool $cloned = false): DictUtil
    {
        if ($dict instanceof DictUtil) {
            return $dict;
        }
        $dictUtil = null;
        $key = CacheConst::dict($dict, $appType);
        if (!isset($this->item[$key])) {
            // 内存中不存在字典数据
            if (!cache("?{$key}")) {
                // 缓存中也不存在，则读取数据库
                $dictModel = DictModel::find($dict);
                if (\is_null($dictModel)) {
                    // 未查询到有效数据
                    throw new CmsException(
                        ErrCodeFacade::getJError(
                            40,
                            [
                                'id' => $dict,
                            ]
                        )
                    );
                }
                // 查询dictitem数据
                $dictItemModel = DictItemModel::where('di_dict', $dict)->where('di_app_type', 'in', [0, $appType])
                    ->order('di_show_order asc  ,di_id asc')->select();
                if (\is_null($dictItemModel)) {
                    // 未查询到有效数据
                    throw new CmsException(
                        ErrCodeFacade::getJError(
                            41,
                            [
                                'id' => $dict,
                                'name' => 'all',
                            ]
                        )
                    );
                }
                // 读取到的数据做处理
                $dictUtil = new DictUtil($dictModel->toArray());
                $dictUtil->loadItem($dictItemModel->toArray());
                cache("{$key}", $dictUtil);
            } else {
                $dictUtil = cache("{$key}");
            }
            $this->item[$key] = $dictUtil;
        } else {
            $dictUtil = $this->item[$key];
        }
        // 如果需要返回克隆对象，则通过序列化做一次深拷贝
        return $cloned ? \unserialize(\serialize($dictUtil)) : $dictUtil;
    }

    /**
     * 根据表名获取字典数据
     *
     * @param string  $tableName 表名
     * @param integer $appType   应用类型
     *
     * @return DictUtil
     */
    public function getDictByTableName(string $tableName, int $appType = 0): DictUtil
    {
        $key = "{$tableName}-{$appType}";
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
        $this->itemName[$key] = $this->getDict($dictId, $appType);
        return $this->itemName[$key];
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
     * @param integer|DictUtil $dict  字典id或字典对象
     * @param string  $fieldName  字典项字段名
     * @param integer $appType  应用类型
     *
     * @return DictItem
     * @throws CmsException 字典项不存在
     */
    public function getDictItem($dict, string $fieldName, int $appType = 0): DictItemUtil
    {
        $dictUtil = $this->getDict($dict, $appType);
        $fieldName = Helper::delPrefix($fieldName, $dictUtil->prefix);
        $dictItemUtil = $dictUtil->getItem($fieldName);
        if (\is_null($dictItemUtil)) {
            // 获取不到有效的字典项数据，抛出异常
            throw new CmsException(
                ErrCodeFacade::getJError(
                    41,
                    [
                        'id' => $dict,
                        'name' => Helper::delPrefix($fieldName, $dictUtil->prefix),
                    ]
                )
            );
        }
        return $dictItemUtil;
    }

    /**
     * 获取dict_item数量
     *
     * @param integer|Dict $dict  字典id或字典对象
     * @param integer $appType 应用类型
     *
     * @return integer
     */
    protected function getItemCount($dict, int $appType = 0): int
    {
        $dictUtil = \is_numeric($dict) ? $this->getDict($dict, $appType) : $dict;
        return $dictUtil->itemCount();
    }

    /**
     * 获取模型对象
     *
     * @param integer|Dict $dict 字典id或字典对象
     * @param integer $appType 应用类型
     * @return Model 实例化的模型
     * @throws \Throwable 抛出找不到模型异常
     */
    public function getModel($dict, int $appType = 0): BaseModel
    {
        $dictUtil = $this->getDict($dict, $appType);
        $modelName = $dictUtil->tablename;
        // 拼凑类完整命名 确认文件是否存在
        $module = app('http')->getName();
        return Helper::model($modelName, false, $module);
    }

    /**
     * 校验数据
     *
     * @param integer|Dict $dict  字典id或字典对象
     * @param integer $curd 操作类型
     * @param array $data 数据
     * @param boolean $batch 是否批量
     * @param integer $appType 应用类型
     *
     * @return JsonTable
     */
    public function checkData($dict, int $curd, array $data, bool $batch = false, int $appType = 0): JsonTable
    {
        $dict = $this->getDict($dict, $appType);
        $error = []; // 错误信息
        $dict->eachItem(
            function (string $fieldName, DictItemUtil $item) use ($curd, $data, $batch, &$error) {
                try {
                    // 主键跳过，外显字段跳过
                    if (($item->pk && $item->autoed) || ($item->show_dict > 0)) {
                        return true;
                    }
                    // 判断当前curd模式是否需要校验当前字典项
                    if ($curd !== ($curd & $item->curd)) {
                        return true;
                    }

                    // 判断当前curd模式是否需要校验当前字典项必填
                    $state = ($curd === ($curd & 6));
                    $required = ($curd == ($curd & $item->required));
                    if ($state && $required && (!isset($data[$fieldName]) || '' === $data[$fieldName])) {
                        throw new CmsException(
                            ErrCodeFacade::getJError(
                                33,
                                [
                                    'name' => $item->name,
                                ]
                            )
                        );
                    }

                    if (!isset($data[$fieldName])) {
                        // 未传递该参数则跳过剩余校验
                        return true;
                    }
                    if ('' !== $item->show_table) {
                        // 外显字段不参与校验
                        return true;
                    }
                    $itemData = $data[$fieldName];
                    $value = 0;
                    // 校验大小或者长度
                    if ($item->max >= $item->min) {
                        switch ($item->type) {
                            case 6: // 字符串
                                $value = Str::length($itemData);
                            case 1: // 整数
                            case 2: // 小数
                            case 3: // 日期
                            case 4: // 时间
                            case 5: // 日期时间
                                if ($value > $item->max) {
                                    throw new CmsException(
                                        ErrCodeFacade::getJError(
                                            30,
                                            [
                                                'name' => $item->name,
                                                'max' => $item->max,
                                            ]
                                        )
                                    );
                                }
                                if ($value < $item->min) {
                                    throw new CmsException(
                                        ErrCodeFacade::getJError(
                                            31,
                                            [
                                                'name' => $item->name,
                                                'min' => $item->min,
                                            ]
                                        )
                                    );
                                }
                                break;
                            default:
                                break;
                        }
                    }


                    // 如果是字符串类型，还需要做正则校验
                    if (6 == $item->type) {
                        if ('' != $item->regex) {
                            if (isset($data[$fieldName]) && '' !== $data[$fieldName]) {
                                if (0 === preg_match($item->regex, $itemData)) {
                                    throw new CmsException(
                                        ErrCodeFacade::getJError(
                                            32,
                                            [
                                                'name' => $item->name,
                                                'content' => $item->regex_msg,
                                            ]
                                        )
                                    );
                                }
                            }
                        } else {
                            //去掉前后空格
                            // $data[$fieldName] = trim($data[$fieldName]);
                            // 校验字符串子类型
                        }
                    }
                } catch (CmsException $ex) {
                    // 针对CmsException做处理，批量时只记录不退出
                    if (false === $batch) {
                        $error = [
                            'state' => $ex->getCode(),
                            'msg' => $ex->getMessage(),
                            'data' => $ex->getData(),
                        ];
                        return false;
                    } else {
                        $error[$ex->getCode()] = $ex->getMessage();
                    }
                }
            }
        );
        return empty($error)
            ? $this->jsonTable->success()
            : ($batch
                ? $this->jsonTable->error('error', 1, $error)
                : $this->jsonTable->error(
                    $error['msg'],
                    $error['state'],
                    $error['data']
                ));
    }

    /**
     * 查询数据
     *
     * @param integer|Dict $dict 字典id或字典对象
     * @param array $condition 表达式
     * @param array|null $order 排序，若非null则覆盖字典的排序配置
     * @param string  $fuzzy 模糊查询内容
     * @param integer $currPage 查询的页码
     * @param integer $pageNum 每页数据量，为0则代表不分页
     * @param integer $appType 应用类型
     * @return JsonTable
     */
    public function select(
        $dict,
        array $condition = [],
        ?array $order = null,
        string $fuzzy = null,
        int $currPage = 1,
        int $pageNum = 20,
        int $appType = 0
    ): JsonTable {
        try {
            $query = $this->build($dict, 1, $condition, $order, $fuzzy, $appType);
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
     * @param integer|Dict $dict 字典id或字典对象
     * @param string|integer $id 主键值
     * @param array|null $order 排序，若非null则覆盖字典的排序配置
     * @param integer $appType 应用类型
     * @return JsonTable
     */
    public function findByPrimaryKey($dict, $id, ?array $order = null, int $appType = 0): JsonTable
    {
        try {
            // 获取主数据字典项
            $dict = $this->getDict($dict);
            // 获取主数据
            $pk = $dict->getPrimaryKey();
            if (\is_null($pk)) {
                return ErrCodeFacade::getJError(41);
            }
            // 查询数据
            return $this->find(
                $dict,
                [
                    $pk->fieldname => $id,
                ],
                $order,
                $appType
            );
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 获取指定条件的单一数据
     *
     * @param integer|Dict $dict 字典id或字典对象
     * @param array $condition 条件表达式
     * @param array|null $order 排序，若非null则覆盖字典的排序配置
     * @param integer $appType 应用类型
     * @return JsonTable
     */
    public function find($dict, array $condition = [], ?array $order = null, int $appType = 0): JsonTable
    {
        try {
            $query = $this->build($dict, 8, $condition, $order, null, $appType);
            $data = $query->find();
            if (\is_null($data)) {
                return ErrCodeFacade::getJError(25, ['name' => self::getNameByModel($this->getModel($dict))]);
            }
            return $this->jsonTable->successByData($data->toArray());
        } catch (\Throwable $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 根据条件更新数据
     *
     * @param integer|Dict $dict 字典id或字典对象
     * @param array $data 数据
     * @param array $condition 条件表达式
     * @param integer $appType 应用类型
     * @return JsonTable
     */
    public function update($dict, array $data, array $condition = [], int $appType = 0): JsonTable
    {
        try {
            // 处理数据前缀
            $dict = $this->getDict($dict, $appType);
            $data = Helper::addPrefixArr($data, $dict->prefix);
            // 数据校验
            if (!($jResult = $this->checkData($dict, 4, $data, false, $appType))->isSuccess()) {
                return $jResult;
            }
            // 获取处理后的query对象
            $query = $this->build($dict, 4, $condition, null, null, $appType);
            $model = $query->find();
            if (\is_null($model)) {
                return ErrCodeFacade::getJError(25, ['name' => self::getNameByModel($this->getModel($dict))]);
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
     * @return JsonTable
     */
    public function save($dict, array $data = [], int $appType = 0): JsonTable
    {
        try {
            // 处理数据前缀
            $dict = $this->getDict($dict, $appType);
            $data = Helper::addPrefixArr($data, $dict->prefix);
            // 数据校验
            if (!($jResult = $this->checkData($dict, 2, $data, false, $appType))->isSuccess()) {
                return $jResult;
            }
            $query = $this->build($dict, 2, [], null, null, $appType);
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
     * @return JsonTable
     */
    public function delete($dict, array $condition = [], int $appType = 0): JsonTable
    {
        try {
            $query = $this->build($dict, 16, $condition, null, null, $appType);
            $model = $query->find();
            if (\is_null($model)) {
                return ErrCodeFacade::getJError(25, ['name' => self::getNameByModel($this->getModel($dict))]);
            }
            return $model->delete() ? $this->jsonTable->success() : ErrCodeFacade::getJError(23);
        } catch (PDOException $ex) {
            Helper::logListenCritical(static::class, __FUNCTION__, $ex);
            return ErrCodeFacade::getJError(21);
        } catch (\Exception $ex) {
            return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 返回构建后的Query对象
     *
     * @param integer|Dict $dict 字典id或字典对象
     * @param integer $curd 构建类型，1新增；2修改；4读取；8删除
     * @param array $condition 条件表达式
     * @param array|null $order 排序，若非null则覆盖字典的排序配置
     * @param string $fuzzy 模糊查询内容
     * @param integer $appType 应用类型
     * @return Query
     */
    public function build(
        $dict,
        int $curd,
        array $condition = [],
        ?array $order = null,
        string $fuzzy = null,
        int $appType = 0
    ): Query {
        // 获取字典对应模型
        $model = $this->getModel($dict, $appType);
        $dict = $this->getDict($dict, $appType);
        $tablePrefix = env('database.prefix', '');
        $tableName = $tablePrefix . parse_name($dict->tablename);
        $fieldPrefix = $model->getPrefix();
        $dictItemAll = $dict->getItemAll(true); // 默认要true，提高效率
        $fields = []; //待查询的字段列表
        $joins = []; // 关联查询的信息
        $joinDicts = []; // 关联查询的表字典信息
        $orders = []; // 排序数据
        $fuzzyCondition = [];

        foreach ($dictItemAll as $key => $item) {
            // 字段别名处理，去除字段前缀
            $fieldNameAlias = (0 === strpos($item->fieldname, $fieldPrefix)) ? Helper::delPrefix(
                $item->fieldname,
                $fieldPrefix
            ) : $item->fieldname;
            // 判断下当前处于哪种模式，过滤字段
            if ($curd === ($curd & $item->curd)) {
                // 判断是否读取状态
                $readed = ($curd === ($curd & 9));

                if ($readed) {
                    // 该字段关联外键，且处于刷新1、读取8时才进行处理
                    if ($item->key_dict > 0) {
                        // 外键字段，需要处理下join表数据
                        $joinDict = $this->getDict($item->key_dict);
                        $joinTable = $tablePrefix . parse_name($joinDict->tablename);
                        $joinName = $item->key_join_name ?: $joinTable;
                        $joinField = "{$joinName}.{$item->key_field}";
                        $joinShowField = "{$joinName}.{$item->key_show}";
                        $joinShowFieldAlias = \parse_name(Helper::delPrefix($joinName, $tablePrefix)) . '_' . Helper::delPrefix(
                            $item->key_show,
                            $joinDict->prefix
                        );

                        $joinCondition = $item->key_condition ?: "{$joinField}={$tableName}.{$item->fieldname}";
                        $joinType = $item->join_type ?: 'left';
                        // 写入到待处理的数组中
                        $fields[$joinShowField] = $joinShowFieldAlias;
                        $joins[$joinName] = [
                            'join_table' => [
                                $joinTable => $joinName,
                            ],
                            'join_condition' => $joinCondition,
                            'join_type' => $joinType,
                        ];
                        // 只记录首次的外键表关联信息
                        if (!isset($joinDicts[$item->key_dict])) {
                            $joinDicts[$item->key_dict] = $joinName;
                        }
                    } else if (($item->show_dict > 0) && ('' !== $item->show_field) && (isset($joinDicts[$item->show_dict]))) {
                        // 外显字段，处于刷新1、读取8时才进行处理，且字典排序必须位于外键表后
                        //  && ('' !== $item->show_table) 暂时用不到这个可以忽略
                        // 外显字段，需要判断join表内是否存在外联表
                        $showDict = $this->getDict($item->show_dict);
                        // 如果外键表存在别名设置，则按照别名设置
                        $showTable = $joinDicts[$item->show_dict];
                        $showField = $item->show_field;
                        $showFieldAlias = Helper::delPrefix($showTable, $tablePrefix) . '_' . Helper::delPrefix(
                            $item->show_field,
                            $showDict->prefix
                        );
                        if (isset($joins[$showTable])) {
                            $fields["{$showTable}.{$showField}"] = $showFieldAlias;
                        }
                    } else if (($item->link_dict > 0) && ('' !== $item->link_field) && (isset($joinDicts[$item->link_dict]))) {
                        $linkDict = $this->getDict($item->link_dict);
                        // 如果外键表存在别名设置，则按照别名设置
                        $linkTable = $joinDicts[$item->link_dict];
                        $linkField = $item->link_field;
                        $linkFieldAlias = Helper::delPrefix($linkTable, $tablePrefix) . '_' . Helper::delPrefix(
                            $item->link_field,
                            $linkDict->prefix
                        );

                        if (isset($joins[$linkTable])) {
                            $fields["{$linkTable}.{$linkField}"] = $linkFieldAlias;
                        }
                    }
                    // 设置查询字段别名
                    $fields["{$tableName}.{$item->fieldname}"] = $fieldNameAlias;
                } else {
                    //当修改4和新建2的时候需要判断。是否可编辑
                    $saved = ($curd === ($curd & 6));
                    if ($saved && $curd == $item->readonly) {
                        $fields[] = $item->fieldname;
                    }
                }
                // 排序处理
                if (\is_null($order)) {
                    if ($item->sort > 0) {
                        $orders[str_pad(
                            $item->sort,
                            3,
                            '0',
                            STR_PAD_LEFT
                        ) . "-{$item->id}-{$tableName}.{$item->fieldname}"] = $item->sort % 2 == 1 ? 'asc' : 'desc';
                    }
                }
                if (1 === $curd && $item->fuzzy > 0) {
                    // 列表状态时，才对fuzzy进行处理
                    if (!\is_null($fuzzy) && '' !== $fuzzy) {
                        switch ($item->fuzzy) {
                            case 2: // 右匹配
                                $fuzzyCondition[] = [
                                    'field' => $item->fieldname,
                                    'operator' => 'like',
                                    'value' => "{$fuzzy}%",
                                ];
                                break;
                            case 3: // 左匹配
                                $fuzzyCondition[] = [
                                    'field' => $item->fieldname,
                                    'operator' => 'like',
                                    'value' => "%{$fuzzy}",
                                ];
                                break;
                            case 4: // 左右匹配
                                $fuzzyCondition[] = [
                                    'field' => $item->fieldname,
                                    'operator' => 'like',
                                    'value' => "%{$fuzzy}%",
                                ];
                                break;
                            default:
                                // 全匹配
                                $fuzzyCondition[] = [
                                    'field' => $item->fieldname,
                                    'operator' => '=',
                                    'value' => $fuzzy,
                                ];
                                break;
                        }
                    }
                }
            }
        }

        if (!\is_null($order)) {
            // 传递进来order，则以传递进来为准
            $orders = Helper::addPrefixArr($order, $tableName . '.' . $fieldPrefix);
        } else {
            // 这里再判断下，如果没有配置order，默认增加主键order
            if (empty($orders)) {
                $order["{$tableName}.{$dict->getPrimaryKey()->fieldname}"] = 'desc';
            } else {
                // 处理字典生成的orders
                ksort($orders);
                $kOrder = []; // 排序处理后的order数据
                foreach ($orders as $k => $v) {
                    $arr = explode('-', $k);
                    $kOrder[$arr[2]] = $v;
                }
                $orders = $kOrder;
            }
        }
        // 开始做最后的整理
        // 根据操作类型做字段过滤
        switch ($curd) {
            case 2:
            case 4:
                $query = $model->allowField($fields)->db();
                break;
            case 1: //刷新
            case 8: //读取
                $query = $model->field($fields);
                break;
            default:
                $query = $model->db();
                break;
        }
        // 对条件做一次处理
        foreach ($condition as &$item) {
            // 如果该字段存在于当前字典内，则主动添加前缀头
            if ($dict->exists($item[0], Helper::existsPrefix($item[0], $dict->prefix))) {
                $item[0] = $tableName . '.' . Helper::addPrefix($item[0], $dict->prefix);
            }
        }

        $query = $query->where($condition)->order($orders);
        // 处理模糊查询
        if (!empty($fuzzyCondition)) {
            $query = $query->where(
                function ($qry) use ($fuzzyCondition) {
                    foreach ($fuzzyCondition as $fzyItem) {
                        $qry->whereOr($fzyItem['field'], $fzyItem['operator'], $fzyItem['value']);
                    }
                }
            );
        }
        foreach ($joins as $joinName => $joinItem) {
            // 循环每个关联配置项，然后执行join
            $query = $query->join($joinItem['join_table'], $joinItem['join_condition'], $joinItem['join_type']);
        }
        return $query;
    }
}
