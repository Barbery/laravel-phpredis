<?php

namespace Barbery\Providers;

use Redis;
use RedisCluster;

class Database extends \Illuminate\Redis\Database
{
    private $_optionsKey = ['prefix' => Redis::OPT_PREFIX, 'failover' => RedisCluster::FAILOVER_ERROR];


    protected function createAggregateClient(array $servers, array $options = [])
    {
        $options += array(
            'lazy_connect' => true,
            'pconnect'     => false,
            'timeout'      => 0,
        );

        $cluster = array();
        foreach ($servers['clusterConfig'] as $key => $server) {
            if (isset($this->_optionsKey[$key])) {
                continue;
            }

            $host = empty($server['host']) ? '127.0.0.1' : $server['host'];
            $port = empty($server['port']) ? '6379'      : $server['port'];

            if (isset($server['persistent'])) {
                $options['pconnect'] = $options['pconnect'] && $server['persistent'];
            } else {
                $options['pconnect'] = false;
            }

            $cluster[] = "{$host}:{$port}";
        }

        $RedisCluster = new RedisCluster(null, $cluster);
        foreach ($this->_optionsKey as $key => $option) {
            if (!empty($servers['clusterConfig'][$key])) {
                $RedisCluster->setOption($option, $servers['clusterConfig'][$key]);
            }
        }

        return array('default' => $RedisCluster);
    }


    protected function createSingleClients(array $servers, array $options = [])
    {
        $clients = array();

        foreach ($servers as $key => $server) {
            if ($key === 'cluster') continue;

            $redis = new Redis();

            $host    = empty($server['host'])    ? '127.0.0.1' : $server['host'];
            $port    = empty($server['port'])    ? '6379'      : $server['port'];
            $timeout = empty($server['timeout']) ? 0           : $server['timeout'];

            if (isset($server['persistent']) && $server['persistent']) {
                $redis->pconnect($host, $port, $timeout);
            } else {
                $redis->connect($host, $port, $timeout);
            }

            if (!empty($server['prefix'])) {
                $redis->setOption(Redis::OPT_PREFIX, $server['prefix']);
            }

            if (!empty($server['database'])) {
                $redis->select($server['database']);
            }

            $clients[$key] = $redis;
        }

        return $clients;
    }
}