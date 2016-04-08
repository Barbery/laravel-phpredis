<?php

namespace Barbery\Extensions;

use Illuminate\Support\Arr;

class CacheManager extends \Illuminate\Cache\CacheManager
{
    /**
     * Create an instance of the Redis cache driver.
     *
     * @param  array  $config
     * @return \Barbery\Extensions\RedisStore
     */
    protected function createRedisDriver(array $config)
    {
        $redis = $this->app['redis'];

        $connection = Arr::get($config, 'connection', 'default');

        return $this->repository(new RedisStore($redis, $this->getPrefix($config), $connection));
    }
}