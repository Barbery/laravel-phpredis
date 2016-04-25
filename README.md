# laravel-phpredis
this is the library provide phpredis support in the laravel framework, clearly it support redis cluster too.

## feature
1. this library implement redis driver in Cache and Session, you can easy to use it. 
2. implement redis pipeline just like the laravel document introduce
3. change Cache putMany method to use pipeline to improve performance


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
        // may be you can rename it to MyRedis, So, you can use it like that: 
        // MyRedis::get('key'); MyRedis::set('key', 'value'); MyRedis::pipeline(function($pipe){YOUR_CODE});
        'MyRedis' => Illuminate\Support\Facades\Redis::class,
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
            // cluster options
            'options' => [
                'failover' => env('REDIS_CLUSTER_FAILOVER', RedisCluster::FAILOVER_ERROR),
                'read_timeout' => env('REDIS_CLUSTER_READ_TIMEOUT', 3),
                'timeout' => env('REDIS_CLUSTER_TIMEOUT', 3),
                'persistent' => env('REDIS_CLUSTER_PERSISTENT', false),
            ],
            // put your cluster master node here
            [
                'host' => env('REDIS_HOST_1'),
                'port' => env('REDIS_PORT_1'),
            ],
            [
                'host' => env('REDIS_HOST_2'),
                'port' => env('REDIS_PORT_2'),
            ],
            [
                'host' => env('REDIS_HOST_3'),
                'port' => env('REDIS_PORT_3'),
            ],
        ],
    ],

```
