<?php

declare(strict_types=1);

namespace alocms\controller;

use alocms\extend\dict\util\Dict as DictUtil;
use alocms\logic\Dict as DictLogic;;

use alocms\logic\Dynamic as DynamicLogic;
use alocms\logic\Session as SessionLogic;
use alocms\util\Helper;

/**
 * 动态Api接口基类
 * @author alone <alone@alonetech.com>
 */
class DynamicApi extends Base
{
    /**
     * 字典对象
     * @var DictUtil
     */
    protected $dict = null;
    /**
     * 会话逻辑类
     * @var SessionLogic
     */
    protected $sessionLogic = null;

    /** @inheritDoc */
    protected function initialize(): void
    {
        parent::initialize();
        $this->sessionLogic = SessionLogic::instance();
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
                $item[0] = Helper::addPrefix($item[0], $this->getDict()->prefix);
            }
        }
        return $condition;
    }
    /**
     * 获取当前动态页面字典
     *
     * @return DictUtil
     * @throws \think\exception\HttpResponseException
     */
    protected function getDict(): DictUtil
    {
        if (\is_null($this->dict)) {
            if (!($jResult = DynamicLogic::instance()->getDictByUri($this->request->baseUrl()))->isSuccess()) {
                $this->jexception($jResult);
            }
            $this->dict = $jResult->data;
        }
        return $this->dict;
    }

    /**
     * 列表接口，获取指定页面对应字典的列表数据
     *
     * @return string|array|response
     */
    public function index()
    {
        // 处理前端传递来的条件表达式
        $condition = $this->procCondition();
        // 获取前端传递参数
        $order = $this->request->param('order/s', null);
        $currPage = $this->request->param('p/d', 1);
        $pageNum = $this->request->param('num/d', 20);
        $fuzzy = $this->request->param('key/s', null);
        if (!\is_null($order)) {
            // 存在提交的有效order
            // 解码
            $order = \json_decode(\base64_decode($order), true);
        }
        $dict = $this->getDict();
        // 执行查询
        $jResult = DictLogic::instance()->select(
            $dict,
            $condition,
            $order,
            $fuzzy,
            $currPage,
            $pageNum,
            $this->request->appType()
        );
        if (!$jResult->isSuccess()) {
            return $jResult;
        }
        // 因为需要二次处理，所以取出查询的结果
        $msg = $jResult->msg;
        $data = $jResult->data;
        //数据返回
        return $this->jsonTable->success($msg, $data);
    }
    /**
     * 读取接口，获取指定页面对应字典的详细数据
     *
     * @param integer $id
     * @return string|array|response
     */
    public function read(int $id)
    {
    }
    /**
     * 保存接口，保存指定页面对应字典的详细数据
     *
     * @return string|array|response
     */
    public function save()
    {
    }
    /**
     * 更新接口，更新指定页面对应字典的详细数据
     *
     * @param integer $id 主键id
     * @return string|array|response
     */
    public function update(int $id)
    {
    }
    /**
     * 删除接口，删除指定页面对应字典的详细数据
     *
     * @param integer $id 主键id
     * @return string|array|response
     */
    public function delete(int $id)
    {
    }
}
