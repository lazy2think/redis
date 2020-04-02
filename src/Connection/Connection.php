<?php
/*
 * @Author: zane
 * @Date: 2020-01-22 16:01:50
 * @LastEditTime: 2020-03-30 14:43:54
 * @Description: 
 */

namespace redis\connection;

use redis\Exception\RedisException;
use redis\Contract\ConnectionInterface;
use Closure;
use Redis;
use RedisCluster;
use PhpHelper;

abstract class Connection implements ConnectionInterface
{
    /**
     * 执行Redis可使用的方法
     *
     * @param string $method
     * @param array $parameters
     * @param boolean $reconnect
     * @return void
     */
    public function command(string $method, array $parameters = [], bool $reconnect = false)
    {
        try {
            $lowerMethod = strtolower($method);
            if (!in_array($lowerMethod, $this->supportedMethods, true)) {
                throw new RedisException(
                    sprintf('Method(%s) is not supported!', $method)
                );
            }
            
            $result = $this->client->{$method}(...$parameters);

        } catch (RedisException $e) {
            // if (!$reconnect && $this->reconnect()) {
            //     return $this->command($method, $parameters, true);
            // }
            throw new RedisException(
                sprintf('Redis command reconnect error(%s)', $e->getMessage())
            );
        }

        return $result;
    }
}