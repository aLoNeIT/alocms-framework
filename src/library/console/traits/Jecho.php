<?php

namespace alocms\library\console\traits;

use alocms\library\util\CmsException;
use alocms\library\util\JsonTable;

/**
 * 输出相关trait
 *
 * @author 王阮强 <wangruanqiang@youzhibo.cn>
 * @date 2020-11-17
 */
trait Jecho
{
    /**
     * 工作id
     *
     * @var integer
     */
    protected $workerId = -1;
    /**
     * 名称
     *
     * @var string
     */
    protected $name = static::class;
    /**
     * 获取名称
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getWorkerId(): string
    {
        return $this->workerId;
    }

    /**
     * 输出内容到控制台
     *
     * @param mix|Exception $mess 输出内容文本，或者是exception对象
     * @param integer $state 状态
     * @param array $data 数据内容
     * @return array 返回数组
     */
    protected function echoMess($mess, $state = 0, $data = []): array
    {
        if ($mess instanceof \Exception) {
            $result = [
                'name' => $this->name,
                'worker' => $this->workerId,
                'time' => date('Y-m-d H:i:s'),
                'state' => $mess->getCode(),
                'msg' => $mess->getMessage(),
                'class' => \class_basename($mess),
            ];
            if ($mess instanceof CmsException) {
                $data = $mess->getData();
                if (!empty($data)) {
                    $result['data'] = $data;
                }
            }
        } elseif ($mess instanceof JsonTable) {
            $result = [
                'name' => $this->name,
                'worker' => $this->workerId,
                'time' => date('Y-m-d H:i:s'),
                'state' => $mess->state,
                'msg' => $mess->msg,
                'data' => $mess->data,
            ];
        } else {
            $result = [
                'name' => $this->name,
                'worker' => $this->workerId,
                'time' => date('Y-m-d H:i:s'),
                'state' => $state,
                'msg' => $mess,
            ];
            if (!empty($data)) {
                $result['data'] = $data;
            }
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE), PHP_EOL;
        return $result;
    }
}
