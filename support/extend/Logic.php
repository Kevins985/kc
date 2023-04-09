<?php

namespace support\extend;
use support\extend\Db;

/**
 * Class Logic
 * @package support
 */
class Logic
{
    /**
     * 获取一个数据库连接
     * @param string $adapter
     * @return \Illuminate\Database\Connection
     */
    public function connection($adapter="mysql"){
        return Db::connection($adapter);
    }
}