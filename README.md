# laravel-phpredis


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
