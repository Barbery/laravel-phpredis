# laravel-phpredis
this is the library provide phpredis support in the laravel framework, clearly it support redis cluster too.

## feature
1. this library implement redis driver in Cache and Session, and Queue, you can easy to use it.
2. implement redis pipeline just like the laravel document introduce
3. you can customize your serilize and unserilize function
4. you can customize your cache expired time default unit

## requirements
* PHP >= 5.6.x
* [PhpRedis](https://github.com/phpredis/phpredis)
* Laravel >= 5.2.x

## install
You can install it by composer, just execute below command

### for laravel >= 5.4
Since laravel 5.4, laravel already support phpredis dirver. So, you don't need to use this library.

### for laravel >= 5.3
```bash
composer require barbery/laravel-phpredis:dev-master
```

### for laravel >= 5.2
```bash
composer require barbery/laravel-phpredis:v5.2.x-dev
```

## config
If you want to completely use phpredis instead of predis... You should add below config to your config/app.php file:
```php
'providers' => [
        // ...
        // YOUR OTHER PROVIDERS SETTING
        // ...
        // And you should commend those system's provider as below
        // Illuminate\Redis\RedisServiceProvider::class,

        // add this to your providers
        Barbery\Providers\RedisServiceProvider::class,
]


aliases => [
        // you must rename the Redis Key name, because it's conflict with the \Redis class provide by phpredis
        // may be you can rename it to MyRedis, So, you can use it like that:
        // MyRedis::get('key'); MyRedis::set('key', 'value'); MyRedis::pipeline(function($pipe){YOUR_CODE});
        'MyRedis' => Illuminate\Support\Facades\Redis::class,
]
```



```php
// add config to `config/database.php`
'redis' => [
        // if this true, will enable redis cluster mode
        'cluster' => env('REDIS_CLUSTER', false),

        // defualt config is for single redis mode not cluster
        'default' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
            // can take one of the values:
            // Redis::SERIALIZER_NONE, 'none',
            // Redis::SERIALIZER_PHP, 'php',
            // Redis::SERIALIZER_IGBINARY, 'igbinary'
            'serializer' => env('REDIS_SERIALIZER', Redis::SERIALIZER_PHP),
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


## Advance config
The default unit in laravel is minutes, if you want to change it, you can add config in your `app/cache.php`
```php
'stores' => [
        // YOUR OTHER SETTING
        // ...

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            // add this config to change the default unit, it can be 'second','minute','hour'
            'defaultUnit' => 'second',
            // if you don't set encodeFunc and decodeFunc,
            // default to use php serialize and unserialize to encode/decode value
            // below setting show you how to change it to use json_encode/json_decode to encode/decode value
            'encodeFunc' => function($value){return json_encode($value);},
            'decodeFunc' => function($value){return json_decode($value, true);},
        ],
    ],
```


## Issue
Because Laravel 5.3.x use Lua script to migrate the queue job, and the redis cluster is not support.
So if you use Laravel 5.3.x with redis cluster mode, then do not use the RedisQueue.
