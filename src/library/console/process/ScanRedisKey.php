<?php

namespace alocms\library\console\process;

use alocms\logic\Account as AccountLogic;
use alocms\logic\AccountDetailTransaction as ADTLogic;
use alocms\logic\Payment as PaymentLogic;
use alocms\model\AccountDetailTransaction as ADTModel;
use alocms\library\util\JsonTable;
use filestorage\facade\FileStorage as FileStorageFacade;

/**
 * 测试系统稳定性使用
 * @author aLoNe.Adams.K <alone@alonetech.com>
 */
class ScanRedisKey extends Api
{

    protected function doProcess(&$data, array &$info): JsonTable
    {
        dump($data, $info);
        /** @var \filestorage\Driver $driver */
        $driver = FileStorageFacade::store('huawei');
        $jResult = $driver->fileSave('/shared/httpd/gjhcml.log', 'public', null, false);
        dump($jResult);
        $jResult = $driver->fileCopy(
            $jResult->data['full_path'],
            'private:my_love'
        );
        dump($jResult);
        //        dump($this->mutex);
        // $redis = Cache::store('redis');
        // $redis->hScan('key', 'ac', function ($data) {
        //     dump($data);
        // });
        // $pay->setConfig([
        //     'secret_key'=>'bd37979d5aec2abe948a52827ec6cc04'
        // ]);

        // dump(OrganizationLogic::instance()->getCode(1,22));
        // return;
        return $this->jsonTable->success();
        $tradeNo = \randStr();
        $authCode = '285753234362669540';
        $result = PaymentLogic::instance()->scan(1, 1, 1, 1, 0.01, $authCode, '测试支付' . \randStr(), '11', '1.80.217.24');
        dump('被扫支付结果', $result);
        if ($result->state == 32) {
            \sleep(5);
            $payResult = $result->result;
            $accountDetailTransaction = $result->account_detail_transaction;
            $adtData = ADTModel::find($accountDetailTransaction['adt_id']);
            $result = PaymentLogic::instance()->query($adtData->corporation->corp_id, $adtData->organization->org_id, $adtData->adt_id);
            dump('订单查询结果', $result);
            dump($result->result);
            $result = ADTLogic::instance()->updateByPayResult($adtData->adt_id, $result->result);
            dump('数据更新结果', $result);
        }
        // $result=PayFacade::scan('QRA290493990FFL', $tradeNo , 1, '288570967189941213', '测试支付'.\randStr(), '1.80.217.24',[
        //     'attach'=>\urlencode(\http_build_query([
        //         'subject'=>'010101',
        //         'account'=>11
        //     ]))
        // ]);
        // dump(\urlencode(\http_build_query([
        //     'subject'=>'010101',
        //     'account'=>11
        // ])));
        // dump($result);
        // \sleep(5);
        // $result=PayFacade::reverse('QRA290493990FFL','AD610101000120201201000001');
        // dump($result);
        // $pay=new Pay(app());
        // if(cache('?refund_no')){
        //     $refundNo=cache('refund_no');
        // }else{
        //     $refundNo=\randStr();
        //     cache('refund_no',$refundNo);
        // }
        $refundNo = \randStr();
        // $result=PayFacade::refund('QRA290493990FFL',$tradeNo,$refundNo,1,1);
        // $result=PayFacade::queryRefund('QRA290493990FFL',$tradeNo,$refundNo);
        // $payResult=$result['data'];
        //dump($result);

        // $xmlData=[
        //     'root'=>[
        //         'parent'=>'hello world',
        //         'teacher'=>'ffffff'
        //     ],
        //     'album'=>'picture'
        // ];
        // $xml=array2Xml('xml',$xmlData);
        // echo $xml,PHP_EOL;
        // $data=xml2array($xml);
        // dump($data);
        // $document=new DOMDocument("1.0","UTF-8");
        // $document->loadXML($xml);
        // echo $document->saveXml(),PHP_EOL;
        // $root=$document->createElement('root');
        // $parent=$document->createElement('parent');
        // $album=$document->createElement('album','hello world');
        // $cdata=$document->createCDATASection('富文本内容');
        // $document->appendChild($root);
        // $root->appendChild($parent);
        // $root->appendChild($album);
        // $parent->appendChild($cdata);
        //echo $document->saveXml(),PHP_EOL;
        $data = AccountLogic::instance()->getDetailInfo(1);
        // dump($data);
        // dump(AccountLogic::instance()->updateCash(1));

        // $subSql = AccountDetailModel::field('1 ad_account,ifnull(sum(ad_payment_cash),0) payment_cash,ifnull(sum(ad_spent_cash),0) spent_cash')
        //     ->where('ad_account', 1)->buildSql();
        // dump($subSql);
        // $sql = AccountModel::alias('a')->leftJoin([$subSql => 'b'], 'b.ad_account=a.acc_id')
        //     ->where('acc_id',1)
        //     ->update([
        //     'acc_payment_cash' => Db::raw('b.payment_cash'),
        //     'acc_spent_cash' => Db::raw('b.spent_cash'),
        // ]);
        // dump($sql);
        // return $this->jsonTable->success();
        // dump(SubjectLogic::instance()->isPayment('1101'));
        // dump($this->testCash(3.2237));
        // $code = AccountLogic::instance()->generateCode('OG6101010001');
        // dump($code);
        // $code = AccountDetailLogic::instance()->generateCode('OG6101010001');
        // dump($code);
        //$jResult=SmsLogic::instance()->sendCode('18991356656');
        //dump($jResult);
        // $pattern = 'lock_deal:*';
        // $redis->scan($pattern, function ($key) use ($redis) {
        //     //每次获取到匹配的键名，则回调
        //     $ttl = $redis->handler()->ttl($key);
        //     if (-1 == $ttl) {
        //         $this->echoMess($key);
        //     }
        //     return true;
        // });
        //获取输入输出
        return $this->jsonTable->success();
    }

    protected function getTask()
    {
        return [
            'task' => 'ScanRedisKey',
        ];
    }

    private function testCash(float $cash): float
    {
        return \bcadd($cash, 4.3312, 4);
    }
}
