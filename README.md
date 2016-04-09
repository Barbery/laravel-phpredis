# laravel-phpredis
this is the library provide phpredis support in the laravel framework, clearly it support redis cluster too.

## install
You can install it by composer, just execute below command
```bash
composer require barbery/laravel-phpredis:dev-master
```

## config
If you want to completely use phpredis instead of predis... You should add below config to your config/app.php file:
```php
'providers' => [
        // ...
        // YOUR OTHER PROVIDERS SETTING 
        // ...
        // And you should commend those system's provider as below
        // Illuminate\Cache\CacheServiceProvider::class,
        // Illuminate\Redis\RedisServiceProvider::class,

        // add this to your providers
        Barbery\Providers\RedisServiceProvider::class,
        Barbery\Providers\CacheServiceProvider::class,
]


aliases => [
        // you must rename the Redis Key name, because it's conflict with the \Redis class provide by phpredis
        // may be you can rename it to MyRedis, So, you can use it like that: MyRedis::get('key'); MyRedis::set('key', 'value');
        'Redis' => Illuminate\Support\Facades\Redis::class,
]
```



```php
// add config to config/database.php
'redis' => [
        // if this true, will enable redis cluster mode
        'cluster' => env('REDIS_CLUSTER', false),

        // defualt config is for single redis mode not cluster
        'default' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

        // this is for redis cluster mode
        'clusterConfig' => [
            // failover setting
            'failover' => RedisCluster::FAILOVER_ERROR,
            [
                'host' => '127.0.0.1',
                'port' => 6379,
            ],
            [
                'host' => '127.0.0.1',
                'port' => 6380,
            ],
            [
                'host' => '127.0.0.1',
                'port' => 6381,
            ],
            [
                'host' => '127.0.0.1',
                'port' => 6382,
            ],
            [
                'host' => '127.0.0.1',
                'port' => 6383,
            ],
            [
                'host' => '127.0.0.1',
                'port' => 6384,
            ],
        ],
    ],

```
