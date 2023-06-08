<?php

namespace alocms\console\process;

use alocms\util\Helper;
use alocms\util\JsonTable;
use think\facade\Cache;
use XCron\CronExpression;

/**
 * 定时任务清理
 *
 * @author aLoNe.Adams.K <alone@alonetech.com>
 * @date 2020-02-28
 */
class CronClear extends CronBase
{

    /**
     * 定时任务信息
     *
     * @var array
     */
    protected $cronTask = [];

    /** @inheritDoc */
    protected function initialize(): void
    {
        parent::initialize();
        $config = $this->config['crontab'];
        // 读取timer配置信息
        $i = 0;
        foreach ($config as $key => $val) {
            $cron = CronExpression::factory($key);
            $time = $cron->getNextRunDate()->getTimestamp();

            if (!is_array($val)) {
                $val = [$val];
            }
            foreach ($val as $item) {
                $this->cronTask[$i]['key'] = $key;
                $this->cronTask[$i]['val'] = $item;
                $this->cronTask[$i]['next_time'] = $time;
                $this->cronTask[$i]['cron'] = $cron;
                $this->cronTask[$i]['last_time'] = 0;
                $i++;
            }
        }
    }
    /** @inheritDoc */
    public function doProcess(&$data, array &$info): JsonTable
    {
        try {
            foreach ($this->cronTask as &$one) {
                Cache::store('redis')->delete($this->getLockName($one['val']));
            }
            return $this->jsonTable->success();
        } catch (\Throwable $ex) {
            return Helper::logListenException(static::class, __FUNCTION__, $ex);
        }
    }

    /** @inheritDoc */
    protected function getTask()
    {
        static $first = true;
        if ($first) {
            $first = false;
            return true;
        }
        return false;
    }
}
