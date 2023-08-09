<?php

declare(strict_types=1);

namespace alocms\controller;

use alocms\extend\dict\util\Dict as DictUtil;
use alocms\facade\ErrCode as ErrCodeFacade;
use alocms\logic\Dict as DictLogic;
use alocms\model\Base as BaseModel;
use alocms\util\Helper;
use alocms\util\JsonTable;
use think\facade\Db as Db;

/**
 * 通用Api基类
 * Controller只做请求相关处理，返回信息处理
 * 数据库操作交给Model
 * 业务逻辑交给Logic
 * 同类型业务逻辑组合交给Process
 */
class CommonApi extends Api
{
    /**
     * 应用类型
     * 
     * @var integer
     */
    protected $appType = null;

    /**
     * 字典id
     *
     * @var integer
     */
    protected $dictId = 0;

    /**
     * 字典对象
     *
     * @var DictUtil
     */
    protected $dict = null;

    /**
     * 固定用的过滤属性，会覆盖前端提交过来的参数
     *
     * @var array
     */
    protected $filter = [];

    /**
     * 事务开关，1刷新；2新增；4修改；8读取；16删除
     *
     * @var integer
     */
    protected $transaction = 0;

    /**
     * 不需要处理前缀的字段数组
     * @var array
     */
    protected $excludePrefix = [];

    /**
     * 会话逻辑类
     *
     * @var SessionLogic
     */
    protected $sessionLogic = null;

    /**
     * 添加排序信息
     *
     * @var array
     */
    protected $order = null;

    /**
     * @inheritDoc
     */
    protected function initialize(): void
    {
        parent::initialize();
    }

    /**
     * 获取字典对象
     *
     * @return DictUtil
     */
    protected function getDict(): DictUtil
    {
        if (\is_null($this->dict)) {
            $this->dict = DictLogic::instance()->getDict($this->dictId, $this->getAppType());
        }
        return $this->dict;
    }

    /**
     *  getAppType 获取应用类型
     *
     * @return int
     */
    protected function getAppType(): int
    {
        if (\is_null($this->appType)) {
            $this->appType = $this->request->appType();
        }
        return $this->appType;
    }

    /**
     * 获取需要的模型
     *
     * @return BaseModel
     */
    protected function getModel(): BaseModel
    {
        return DictLogic::instance()->getModel($this->dict);
    }

    /**
     * 处理前端condition条件
     *
     * @return array 返回处理后的参数数组
     */
    protected function procCondition(): array
    {
        $condition = [];
        // 处理前端传递过来的返回值
        if ($this->request->has('condition', 'get')) {
            $condition = json_decode(base64_decode($this->request->get('condition')), true);
            foreach ($condition as &$item) {
                if (is_array($item[0])) {
                    continue;
                }
                $item[0] = Helper::addPrefix($item[0], $this->getDict()->prefix, $this->excludePrefix);
            }
        }
        return $condition;
    }
    /**
     * 过滤、掉件合并处理
     *
     * @param array $filter 过滤表达式['keyTemp'=>['key',condtion,value]]  keyTemp不关注值，只为了区分！
     * @param array $condition 条件表达式
     * @return array 返回合并后的表达式
     */
    protected function filterCondition(array $filter, array $condition): array
    {
        $temp = [];
        foreach (array_merge($condition, $filter) as $key => $item) {
            // 每个元素都是array，且第一个元素为字段名
            if (!\is_array($item)) {
                continue;
            }
            $temp[$key] = $item;
        }
        // 获取新数组中的value组成新的下标数组
        return array_values($temp);
    }

    /**
     * 检查当前操作是否需要开启事务
     *
     * @param int $curd 操作类型
     * @return boolean
     */
    protected function checkTransaction(int $curd): bool
    {
        return $curd == ($curd & $this->transaction);
    }
    /**
     * 执行事务操作
     *
     * @param integer $curd crud类型
     * @param callable $callback 回调函数，该回调函数必须返回JsonTable对象
     * @return JsonTable 返回JsonTable对象结果
     */
    protected function executeTransaction(int $curd, callable $callback): JsonTable
    {
        if (!$this->checkTransaction($curd)) {
            // 非事务，则直接执行
            return $callback($this);
        } else {
            // 执行事务
            $connection = Db::connect();
            $connection->startTrans();
            try {
                $jResult = Helper::throwifJError($callback($this));
                $connection->commit();
                return $jResult;
            } catch (\Throwable $ex) {
                $connection->rollback();
                return Helper::logListenCritical(static::class, __FUNCTION__, $ex);
            }
        }
    }

    /**
     * 列表页
     *
     * @return string|array 返回数据并输出给浏览器
     */
    public function index()
    {
        return $this->jecho($this->executeTransaction(1, function () {
            return $this->doIndex();
        }));
    }

    /**
     * 列表页操作前处理
     *
     * @param array $condition 条件表达式
     * @return JsonTable 返回JsonTable对象
     */
    protected function beforeIndex(array &$condition): JsonTable
    {
        return $this->jsonTable->success();
    }
    /**
     * 执行列表查询功能
     *
     * @return string 返回json字符串
     */
    protected function doIndex(): JsonTable
    {
        // 处理前端传递来的条件表达式
        $condition = $this->procCondition();
        // 前置操作，允许对表达式进行处理，或者其他处理
        $jResult = $this->beforeIndex($condition);
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        // 获取前端传递参数
        $order = $this->request->param('order/s', null);
        $currPage = $this->request->param('p/d', 1);
        $pageNum = $this->request->param('num/d', 20);
        $fuzzy = $this->request->param('key/s', null);
        if (!\is_null($order)) {
            // 存在提交的有效order
            // 解码
            $order = \json_decode(\base64_decode($order), true);
            $this->order = empty($order) ? $this->order : array_merge($order, $this->order ?? []);
        }
        // 执行查询
        $jResult = DictLogic::instance()->select(
            $this->dictId,
            $this->filterCondition($this->filter, $condition),
            $this->order,
            $fuzzy,
            $currPage,
            $pageNum,
            $this->getAppType()
        );
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        // 因为需要二次处理，所以取出查询的结果
        $msg = $jResult->msg;
        $data = $jResult->data;
        $jResult = $this->afterIndex($msg, $data);
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        //数据返回
        return $this->jsonTable->success($msg, $data);
    }

    /**
     * 列表后操作处理
     *
     * @param array $name 分页数据
     * @param array $data 数据
     * @return JsonTable 返回JsonTable对象
     */
    protected function afterIndex(array &$msg, array &$data): JsonTable
    {
        return $this->jsonTable->success();
    }
    /**
     * 数据保存
     *
     * @return string|array 返回数据并输出给浏览器
     */
    public function save()
    {
        return $this->jecho($this->executeTransaction(2, function () {
            return $this->doSave();
        }));
    }
    /**
     * 数据保存前处理
     *
     * @param array $data 请求传递的保存数据
     * @return JsonTable 返回JsonTable对象
     */
    protected function beforeSave(array &$data): JsonTable
    {
        return $this->jsonTable->success();
    }
    /**
     * 执行数据保存操作
     *
     * @return JsonTable 返回JsonTable对象
     */
    protected function doSave(): JsonTable
    {
        $dictLogic = DictLogic::instance();
        $data = $this->request->post();
        if (empty($data)) {
            return ErrCodeFacade::getJError(20);
        }
        // 新增前处理
        $jResult = $this->beforeSave($data);
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        // 数据新增
        $jResult = $dictLogic->save($this->dictId, $data, $this->getAppType());
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        // 新增后置处理
        $pkValue = $jResult->msg;
        $data = $jResult->data;
        $jResult = $this->afterSave($jResult->msg, $data);
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        return $this->jsonTable->success($pkValue, $data);
    }
    /**
     * 数据保存后处理
     *
     * @param mixed $id 主键id
     * @param array $data 请求传递的保存数据
     * @return JsonTable 返回JsonTable对象
     */
    protected function afterSave($id, array &$data): JsonTable
    {
        return $this->jsonTable->success();
    }
    /**
     * 数据读取
     *
     * @param integer|string $id 数据主键值
     * @return string|array 返回数据并输出给浏览器
     */
    public function read($id)
    {
        return $this->jecho($this->executeTransaction(8, function () use ($id) {
            return $this->doRead($id);
        }));
    }
    /**
     * 读取数据前处理
     *
     * @param integer|string $id 要查询的数据主键值
     * @return JsonTable 返回JsonTable对象
     */
    protected function beforeRead(&$id): JsonTable
    {
        return $this->jsonTable->success();
    }
    /**
     * 执行数据查询操作
     *
     * @param integer|string $id 数据主键值
     * @return JsonTable 返回JsonTable对象
     */
    protected function doRead($id): JsonTable
    {
        $dictLogic = DictLogic::instance();
        // 获取主数据字典项
        $dict = $dictLogic->getDict($this->dictId);
        // 获取主数据
        $pk = $dict->getPrimaryKey();
        if (\is_null($pk)) {
            return ErrCodeFacade::getJError(41);
        }
        // 读取前处理
        $jResult = $this->beforeRead($id);
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        // 主键查询条件
        $condition = [
            [$pk->fieldname, '=', $id],
        ];
        $jResult = DictLogic::instance()->find($this->dictId, $this->filterCondition($this->filter, $condition), null, $this->getAppType());
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        // 读取后处理
        $data = $jResult->data;
        $jResult = $this->afterRead($id, $data);
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        return $this->jsonTable->successByData($data);
    }
    /**
     * 数据读取后处理
     *
     * @param mixed $id 主键
     * @param array $data 查询到的数据
     * @return JsonTable 返回JsonTable对象
     */
    protected function afterRead($id, array &$data): JsonTable
    {
        return $this->jsonTable->success();
    }
    /**
     * 数据更新
     *
     * @param integer|string $id 数据主键值
     * @return string|array 返回数据并输出给浏览器
     */
    public function update($id)
    {
        return $this->jecho($this->executeTransaction(4, function () use ($id) {
            return $this->doUpdate($id);
        }));
    }
    /**
     * 数据更新前处理
     *
     * @param integer|string $id 数据主键值
     * @param array $data 请求传递的更新数据
     * @return JsonTable 返回JsonTable对象
     */
    protected function beforeUpdate($id, array &$data): JsonTable
    {
        return $this->jsonTable->success();
    }
    /**
     * 执行数据更新操作
     *
     * @param integer|string $id 数据主键值
     * @return JsonTable 返回JsonTable对象
     */
    protected function doUpdate($id): JsonTable
    {
        $dictLogic = DictLogic::instance();
        $dict = $this->getDict();
        $pk = $dict->getPrimaryKey();
        if (\is_null($pk)) {
            return ErrCodeFacade::getJError(41);
        }
        $data = $this->request->put();
        if (empty($data)) {
            return ErrCodeFacade::getJError(20);
        }
        //更新前处理
        $jResult = $this->beforeUpdate($id, $data);
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        // 更新处理
        $condition = [
            [$pk->fieldname, '=', $id],
        ];
        $jResult = $dictLogic->update($this->dictId, $data, $this->filterCondition($this->filter, $condition), $this->getAppType());
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        //更新后处理
        $data = $jResult->data;
        $jResult = $this->afterUpdate($id, $data);
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        return $this->jsonTable->successByData($data);
    }
    /**
     * 数据更新后
     *
     * @param integer|string $id 数据主键值
     * @param array $data 请求传递的更新数据
     * @return JsonTable 返回JsonTable对象
     */
    protected function afterUpdate($id, array &$data): JsonTable
    {
        return $this->jsonTable->success();
    }
    /**
     * 数据删除
     *
     * @param integer|string $id 数据主键值
     * @return string|array 返回JsonTable对象
     */
    public function delete($id)
    {
        return $this->jecho($this->executeTransaction(16, function () use ($id) {
            return $this->doDelete($id);
        }));
    }
    /**
     * 数据删除前
     *
     * @param integer|string $id 数据主键值
     * @return JsonTable 返回JsonTable对象
     */
    protected function beforeDelete($id): JsonTable
    {
        return $this->jsonTable->success();
    }
    /**
     * 执行数据删除操作
     *
     * @param integer|string $id 数据主键值
     * @return JsonTable 返回JsonTable对象
     */
    protected function doDelete($id): JsonTable
    {
        $dictLogic = DictLogic::instance();
        $dict = $this->getDict();
        $pk = $dict->getPrimaryKey();
        if (\is_null($pk)) {
            return ErrCodeFacade::getJError(41);
        }
        // 删除前处理
        $jResult = $this->beforeDelete($id);
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        // 执行删除
        $condition = [
            [$pk->fieldname, '=', $id],
        ];
        $jResult = $dictLogic->delete($this->dictId, $this->filterCondition($this->filter, $condition), $this->getAppType());
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        //删除后处理
        $jResult = $this->afterDelete($id);
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        return $this->jsonTable->success();
    }
    /**
     * 数据删除后
     *
     * @param integer|string $id 数据主键值
     * @return JsonTable 返回JsonTable对象
     */
    protected function afterDelete($id): JsonTable
    {
        return $this->jsonTable->success();
    }
}
