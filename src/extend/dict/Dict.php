<?php

declare(strict_types=1);

namespace dict;

use alocms\facade\JsonTable as JsonTableFacade;
use alocms\traits\Instance;
use alocms\util\CmsException;
use alocms\util\Helper;
use alocms\util\JsonTable;
use dict\interface\Processor as ProcessorInterface;
use dict\util\Dict as DictUtil;
use dict\util\DictItem as DictItemUtil;
use think\db\Query;
use think\helper\Str;

/**
 * 字典管理类
 */
class Dict
{
    use Instance;
    /**
     * 是否被初始化，用于全局判定是否已经初始化过
     *
     * @var boolean
     */
    private static $initialized = false;

    /**
     * 字典处理器
     *
     * @var ProcessorInterface
     */
    protected $processor = null;

    /** @inheritDoc */
    public function __construct()
    {
        if (!static::$initialized) {
            // 获取当前路径
            $langFile = __DIR__ . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'zh-cn.php';
            // 初始化，加载语言文件
            \app()->lang->load($langFile);
            static::$initialized = true;
        }
    }
    /**
     * 设置字典处理器
     *
     * @param ProcessorInterface $processor 实现了字典处理器的对象
     * @return void
     */
    public function setProcessor(ProcessorInterface $processor)
    {
        $this->processor = $processor;
    }
    /**
     * 获取字典对象
     *
     * @param integer $id 字典id
     * @return DictUtil
     */
    public function getDict(int $id): DictUtil
    {
        // 检查字典处理器状态
        if (is_null($this->processor) || !($this->processor instanceof ProcessorInterface)) {
            Helper::exception(\lang('dict_processor_invalid'));
        }
        // 获取字典
        return $this->processor->getDict($id);
    }

    /**
     * 校验数据
     *
     * @param DictUtil $dict 字典id或字典对象
     * @param integer $curd    操作类型
     * @param array $data 数据
     * @param boolean $batch 是否批量返回错误信息
     *
     * @return JsonTable
     */
    public function checkData(DictUtil $dict, int $curd, array $data, bool $batch = false): JsonTable
    {
        $error = []; // 错误信息
        // 遍历处理每一个字典项
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
                        Helper::exception(\lang('data_required', [
                            'name' => $item->name,
                        ]));
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
                                    Helper::exception(\lang('data_gt_max', [
                                        'name' => $item->name,
                                        'max' => $item->max,
                                    ]));
                                }
                                if ($value < $item->min) {
                                    Helper::exception(\lang('data_lt_min', [
                                        'name' => $item->name,
                                        'min' => $item->min,
                                    ]));
                                }
                                break;
                            default:
                                break;
                        }
                    }
                    // 如果是字符串类型，还需要做正则校验
                    if (6 == $item->type && '' != $item->regex) {
                        if ((isset($data[$fieldName]) && '' !== $data[$fieldName]) && (0 === preg_match($item->regex, $itemData))) {
                            Helper::exception(\lang('data_regex_fail', [
                                'name' => $item->name,
                                'content' => $item->regex_msg,
                            ]));
                        }
                    }
                } catch (CmsException $ex) {
                    // 针对YzbException做处理，批量时只记录不退出
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
            ? JsonTableFacade::success()
            : ($batch
                ? JsonTableFacade::error('error', 1, $error)
                : JsonTableFacade::error(
                    $error['msg'],
                    $error['state'],
                    $error['data']
                ));
    }

    /**
     * 查询数据
     *
     * @param DictUtil $dict 字典id或字典对象
     * @param array $condition 表达式
     * @param array|null $order 排序，若非null则覆盖字典的排序配置
     * @param string  $fuzzy 模糊查询内容
     * @param integer $currPage 查询的页码
     * @param integer $pageNum 每页数据量，为0则代表不分页
     * @param integer $appType 应用类型
     *
     * @return JsonTable
     */
    public function select(
        DictUtil $dict,
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
            return JsonTableFacade::success(
                [
                    'curr' => $currPage,
                    'page' => $totalPage,
                    'num' => $pageNum,
                    'count' => $totalCount,
                ],
                $data->toArray()
            );
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }

    /**
     * 返回构建后的Query对象
     *
     * @param DictUtil $dict 字典id或字典对象
     * @param integer $curd 构建类型，1新增；2修改；4读取；8删除
     * @param array $condition 条件表达式
     * @param array|null $order 排序，若非null则覆盖字典的排序配置
     * @param string $fuzzy 模糊查询内容
     * @param integer $appType 应用类型
     *
     * @return Query
     */
    public function build(
        DictUtil $dict,
        int $curd,
        array $condition = [],
        ?array $order = null,
        ?string $fuzzy = null,
        int $appType = 0
    ): Query {
        // 获取字典对应模型
        $model = $this->processor->getModel($dict, $appType);
        // 获取表前缀
        $tablePrefix = $this->processor->getTablePrefix();
        $tableName = $tablePrefix . parse_name($dict->tablename);
        $fieldPrefix = $dict->prefix;
        $dictItemAll = $dict->getItems();
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
                if (\is_null($order) && ($item->sort > 0)) {
                    $orders[str_pad(
                        $item->sort,
                        3,
                        '0',
                        STR_PAD_LEFT
                    ) . "-{$item->id}-{$tableName}.{$item->fieldname}"] = $item->sort % 2 == 1 ? 'asc' : 'desc';
                }
                if ((1 === $curd && $item->fuzzy > 0) && (!\is_null($fuzzy) && '' !== $fuzzy)) {
                    // 列表状态时，才对fuzzy进行处理
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
        // 排序处理
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
        // 设置查询条件和排序条件
        $query = $query->where($condition)->order($orders);
        // 处理模糊查询
        if (!empty($fuzzyCondition)) {
            $query = $query->where(
                function ($qry) use ($fuzzyCondition) {
                    // 这里需要注意是否和上方的condition冲突
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
