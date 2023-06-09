<?php

declare(strict_types=1);

namespace think\db\connector;

use think\db\connector\Mysql;

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
    public function connect(array $config = [], $linkNum = 0, $autoConnection = false)
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
        if (isset($config['password'])) {
            $config['password'] = \aes_decrypt($config['password']);
        }
        //执行父类方法
        return parent::connect($config, $linkNum, $autoConnection);
    }

    protected function supportSavepoint()
    {
        return false;
    }
}
