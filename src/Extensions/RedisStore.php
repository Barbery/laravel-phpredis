<?php
namespace Barbery\Extensions;

// use Closure;

/**
* 
*/
class RedisStore extends \Illuminate\Cache\RedisStore
{
    protected $defaultUnit;
    protected $encodeFunc;
    protected $decodeFunc;

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string|array  $key
     * @return mixed
     */
    public function get($key)
    {
        $value = $this->connection()->get($this->prefix.$key);
        if ($value !== null && $value !== false) {
            return is_numeric($value) ? $value : ($this->decodeFunc)($value);
        }
    }


    /**
     * Store an item in the cache for a given number of time.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $time
     * @return void
     */
    public function put($key, $value, $time)
    {
        $value = is_numeric($value) ? $value : ($this->encodeFunc)($value);

        $time = max(1, $this->translateToSeconds($time));

        $this->connection()->setex($this->prefix.$key, $time, $value);
    }


    private function translateToSeconds($time)
    {
        $unit = ltrim($time, '0123456789');
        if (empty($unit)) {
            $unit = $this->defaultUnit;
        }

        switch (strtolower($unit)) {
            case 'minutes':
            case 'minute':
            case 'm':
                return (int)$time * 60;

            case 'hours':
            case 'hour':
            case 'h':
                return (int)$time * 3600;

            case 'seconds':
            case 'second':
            case 's':
                return (int)$time;

            default:
                // follow the laravel default unit: minutes
                return (int)$time * 60;
        }
    }


    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param  array  $keys
     * @return array
     */
    public function many(array $keys)
    {
        $return = [];

        $prefixedKeys = array_map(function ($key) {
            return $this->prefix.$key;
        }, $keys);

        $values = $this->connection()->mget($prefixedKeys);

        foreach ($values as $index => $value) {
            $return[$keys[$index]] = is_numeric($value) ? $value : ($this->decodeFunc)($value);
        }

        return $return;
    }



    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function forever($key, $value)
    {
        $value = is_numeric($value) ? $value : ($this->encodeFunc)($value);

        $this->connection()->set($this->prefix.$key, $value);
    }



    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush()
    {
        $isCluster = config('database.redis.cluster');
        if (!$isCluster) {
            parent::flush();
        }
    }



    public function setDefaultUnit($unit)
    {
        $this->defaultUnit = $unit;
        return $this;
    }


    public function setEncodeFunc(Callable $encodeFunc)
    {
        $this->encodeFunc = $encodeFunc;
        return $this;
    }


    public function setDecodeFunc(Callable $decodeFunc)
    {
        $this->decodeFunc = $decodeFunc;
        return $this;
    }
}