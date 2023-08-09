<?php

declare(strict_types=1);

namespace alocms\controller;

use alocms\logic\DictDefine as DictDefineLogic;

/**
 * 码表数据获取
 *
 * @author alone <alone@alonetech.com>
 * @date 2023-08-09
 */
class Dict extends Base
{

    /**
     * 获取字典项数据列表
     *
     * @return string|array
     */
    public function read(int $id)
    {
        $appType = $this->request->get('app_type/d', $this->request->appType());
        $jResult = DictDefineLogic::instance()->getItemList((int)$id, $appType);
        if ($jResult->isSuccess()) {
            // 成功处理，则获取数据
            $data = $jResult->data;
            // 删除敏感数据
            unset($data['prefix']);
            $jResult = $this->jsonTable->successByData($data);
        }
        return $this->jecho($jResult);
    }
    /**
     * 通过uri获取字典项数据列表
     *
     * @param string $uri uri地址,base64编码过
     * @return string|array
     */
    public function uri_read(string $uri)
    {
        $appType = $this->request->get('app_type/d', $this->request->appType());
        $uri = \base64_decode($uri);
        $jResult = DictDefineLogic::instance()->getItemListByUri($uri, $appType);
        if ($jResult->isSuccess()) {
            // 成功处理，则获取数据
            $data = $jResult->data;
            // 删除敏感数据
            unset($data['prefix']);
            $jResult = $this->jsonTable->successByData($data);
        }
        return $this->jecho($jResult);
    }
}
