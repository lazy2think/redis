<?php
/*
 * @Author: zane
 * @Date: 2020-01-22 16:01:50
 * @LastEditTime: 2020-03-25 10:11:27
 * @Description: 
 */

namespace redis\connection;

use redis\Exception\RedisException;
use redis\connection\Connection;

/**
 * 该方式依赖Predis代码扩展包
 * @see https://packagist.org/packages/predis/predis
 */
class PredisConnection extends Connection
{
   
}