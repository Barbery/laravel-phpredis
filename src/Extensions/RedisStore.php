<?php

namespace Barbery\Extensions;

/**
* 
*/
class RedisStore extends \Illuminate\Cache\RedisStore
{

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
            return is_numeric($value) ? $value : json_decode($value, true);
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
        $value = is_numeric($value) ? $value : json_encode($value);

        $time = max(1, $this->translateToSeconds($time));

        $this->connection()->setex($this->prefix.$key, $time, $value);
    }


    private function translateToSeconds($time)
    {
        $unit = substr($time, -1);
        switch (strtolower($unit)) {
            case 'm':
                return (int)$time * 60;

            case 'h':
                return (int)$time * 3600;

            case 's':
            default:
                return (int)$time;
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
            $return[$keys[$index]] = is_numeric($value) ? $value : json_decode($value, true);
        }

        return $return;
    }


    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush()
    {
    }

}