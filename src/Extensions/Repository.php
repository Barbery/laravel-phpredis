<?php
namespace Barbery\Extensions;

class Repository extends \Illuminate\Cache\Repository
{
    /**
     * Determine if an item exists in the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        $value = $this->get($key);
        return $value !== null && $value !== false;
    }



    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->many($key);
        }

        $value = $this->store->get($this->itemKey($key));

        if ($value === null || $value === false) {
            $this->fireCacheEvent('missed', [$key]);

            $value = value($default);
        } else {
            $this->fireCacheEvent('hit', [$key, $value]);
        }

        return $value;
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
        $normalizedKeys = [];

        foreach ($keys as $key => $value) {
            $normalizedKeys[] = is_string($key) ? $key : $value;
        }

        $values = $this->store->many($normalizedKeys);

        foreach ($values as $key => &$value) {
            if ($value === null || $value === false) {
                $this->fireCacheEvent('missed', [$key]);

                $value = isset($keys[$key]) ? value($keys[$key]) : null;
            } else {
                $this->fireCacheEvent('hit', [$key, $value]);
            }
        }

        return $values;
    }



    /**
     * Calculate the number of minutes with the given duration.
     *
     * @param  \DateTime|int  $duration
     * @return int|null
     */
    protected function getMinutes($duration)
    {
        if ($duration instanceof DateTime) {
            $fromNow = Carbon::now()->diffInMinutes(Carbon::instance($duration), false);

            return $fromNow > 0 ? $fromNow : null;
        }

        return $duration;
    }
}