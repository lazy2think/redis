<?php
/*
 * @Author: zane
 * @Date: 2020-03-25 11:29:55
 * @LastEditTime: 2020-03-25 17:20:38
 * @Description: 
 */

namespace redis\Connectors;

use redis\Contract\ConnectorInterface;
use redis\Connection\PhpRedisClusterConnection;
use redis\Connection\PhpRedisConnection;
use Redis;
use RedisCluster;
use redis\Exception\RedisException;

/**
 * @since 1.0.0
 */
class PhpRedisConnector implements ConnectorInterface
{
    /**
     * 创建PhpRedis连接
     *
     * @param array $config
     * @param array $options
     * @return PhpRedisConnection
     */
    public function connect(array $config, array $options)
    {

        $client = new redis();

        $this->establishConnection($client, $config);

        if (! empty($config['password'])) {
            $client->auth($config['password']);
        }

        if (isset($config['database'])) {
            $client->select((int) $config['database']);
        }

        if (! empty($config['prefix'])) {
            $client->setOption(Redis::OPT_PREFIX, $config['prefix']);
        }

        if (! empty($config['read_timeout'])) {
            $client->setOption(Redis::OPT_READ_TIMEOUT, $config['read_timeout']);
        }
        

        return new PhpRedisConnection($client);
        
    }

    /**
     * Create a new clustered PhpRedis connection.
     *
     * @param  array  $config
     * @param  array  $clusterOptions
     * @param  array  $options
     * @return \Illuminate\Redis\Connections\PhpRedisClusterConnection
     */
    public function connectToCluster(array $config, array $clusterOptions, array $options)
    {
        $options = array_merge($options, $clusterOptions, Arr::pull($config, 'options', []));

        return new PhpRedisClusterConnection($this->createRedisClusterInstance(
            array_map([$this, 'buildClusterConnectionString'], $config), $options
        ));
    }

    /**
     * Build a single cluster seed string from array.
     *
     * @param  array  $server
     * @return string
     */
    protected function buildClusterConnectionString(array $server)
    {
        return $server['host'].':'.$server['port'].'?'.Arr::query(Arr::only($server, [
            'database', 'password', 'prefix', 'read_timeout',
        ]));
    }

    /**
     * Establish a connection with the Redis host.
     *
     * @param  \Redis  $client
     * @param  array  $config
     * @return void
     */
    protected function establishConnection($client, array $config)
    {
        $persistent = $config['persistent'] ?? false;
        $persistentId = $config['persistent_id'] ?? null;
        $parameters = [
            $config['host'],
            $config['port'],
            $config['timeout'],
            $persistentId,
            $config['retry_interval'],
        ];

        if (version_compare(phpversion('redis'), '3.1.3', '>=')) {
            $parameters[] = $config['read_timeout'];
        }

        $result = $client->{($persistent ? 'pconnect' : 'connect')}(...$parameters);
        if ($result === false) {
            throw new RedisException(
                sprintf('Redis connect error(%s)',
                json_decode($parameters, JSON_UNESCAPED_UNICODE)
                )
            );
        }
    }

    /**
     * Create a new redis cluster instance.
     *
     * @param  array  $servers
     * @param  array  $options
     * @return \RedisCluster
     */
    protected function createRedisClusterInstance(array $servers, array $options)
    {
        $parameters = [
            null,
            array_values($servers),
            $options['timeout'] ?? 0,
            $options['read_timeout'] ?? 0,
            isset($options['persistent']) && $options['persistent'],
        ];

        if (version_compare(phpversion('redis'), '4.3.0', '>=')) {
            $parameters[] = $options['password'] ?? null;
        }

        return tap(new RedisCluster(...$parameters), function ($client) use ($options) {
            if (! empty($options['prefix'])) {
                $client->setOption(RedisCluster::OPT_PREFIX, $options['prefix']);
            }
        });
    }
}
