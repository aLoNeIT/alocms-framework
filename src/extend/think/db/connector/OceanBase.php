<?php

declare(strict_types=1);

namespace alocms\extend\think\db\connector;

use alocms\util\Helper;
use think\db\connector\Mysql;

/**
 * 阿里云OB数据库连接处理
 */
class OceanBase extends Mysql
{
    /**
     * 连接数据库方法
     * @access public
     * @param  array         $config 连接参数
     * @param  integer       $linkNum 连接序号
     * @param  array|bool    $autoConnection 是否自动连接主数据库（用于分布式）
     * @return PDO
     * @throws Exception
     */
    public function connect(array $config = [], $linkNum = 0, $autoConnection = false): \PDO
    {
        if (isset($this->links[$linkNum])) {
            return $this->links[$linkNum];
        }

        if (!$config) {
            $config = $this->config;
        } else {
            $config = array_merge($this->config, $config);
        }

        //对密码进行解密
        if (isset($config['password']) && isset($config['algorithm']) && is_callable($config['alogorithm'])) {
            $config['password'] = call_user_func($config['alogorithm'], $config['password']);
        }
        //执行父类方法
        return parent::connect($config, $linkNum, $autoConnection);
    }
    /** @inheritDoc */
    protected function supportSavepoint(): bool
    {
        return false;
    }
}
