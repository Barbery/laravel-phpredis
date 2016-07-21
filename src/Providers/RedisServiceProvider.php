<?php

namespace Barbery\Providers;

use Barbery\Extensions\Database;
use Barbery\Extensions\RedisConnector;
use Barbery\Extensions\RedisStore;
use Barbery\Extensions\Repository;
use Cache;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class RedisServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Cache::extend('redis', function ($app, $config) {
            $store = new RedisStore(
                $app['redis'],
                Arr::get($config, 'prefix') ?: $this->app['config']['cache.prefix'],
                Arr::get($config, 'connection', 'default')
            );
            $store->setDefaultUnit(
                Arr::get($config, 'defaultUnit', 'minute')
            )->setEncodeFunc(
                Arr::get($config, 'encodeFunc', 'serialize')
            )->setDecodeFunc(
                Arr::get($config, 'decodeFunc', 'unserialize')
            );

            $repository = new Repository($store);
            return $repository;
        });

        \Queue::extend('redis', function () {
            return new RedisConnector(app('redis'));
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('redis', function ($app) {
            return new Database($app['config']['database.redis']);
        });
    }

    public function provides()
    {
        return [
            'redis',
        ];
    }
}
