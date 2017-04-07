<?php

namespace Barbery\Extensions;

use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Queue\LuaScripts;

class RedisQueue extends \Illuminate\Queue\RedisQueue
{
    public function pop($queue = null)
    {
        $original = $queue ?: $this->default;

        $queue = $this->getQueue($queue);

        $this->migrateExpiredJobs($queue . ':delayed', $queue);

        if (!is_null($this->expire)) {
            $this->migrateExpiredJobs($queue . ':reserved', $queue);
        }

        list($job, $reserved) = $this->_eval(
            LuaScripts::pop(), 2, $queue, $queue . ':reserved', $this->getTime() + $this->expire
        );

        if ($reserved) {
            return new RedisJob($this->container, $this, $job, $reserved, $original);
        }
    }

    /**
     * Delete a reserved job from the reserved queue and release it.
     *
     * @param  string  $queue
     * @param  string  $job
     * @param  int  $delay
     * @return void
     */
    public function deleteAndRelease($queue, $job, $delay)
    {
        $queue = $this->getQueue($queue);

        $this->_eval(
            LuaScripts::release(), 2, $queue . ':delayed', $queue . ':reserved',
            $job, $this->getTime() + $delay
        );
    }

    /**
     * Migrate the delayed jobs that are ready to the regular queue.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    public function migrateExpiredJobs($from, $to)
    {
        $this->_eval(
            LuaScripts::migrateExpiredJobs(), 2, $from, $to, $this->getTime()
        );
    }
    
    /**
     * Get the size of the queue.
     *
     * @param  string  $queue
     * @return int
     */
    public function size($queue = null)
    {
        $queue = $this->getQueue($queue);

        return $this->_eval(LuaScripts::size(), 3, $queue, $queue.':delayed', $queue.':reserved');
    }

    private function _eval($scripts, $keyNumber, ...$args)
    {
        return $this->getConnection()->eval($scripts, $args, $keyNumber);
    }
}
