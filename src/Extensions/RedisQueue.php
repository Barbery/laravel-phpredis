<?php

namespace Barbery\Extensions;

use Illuminate\Queue\Jobs\RedisJob;

class RedisQueue extends \Illuminate\Queue\RedisQueue
{
    protected function getConnection()
    {
        return $this->redis;
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $original = $queue ?: $this->default;

        $queue = $this->getQueue($queue);

        if (!is_null($this->expire)) {
            $this->migrateAllExpiredJobs($queue);
        }

        $job = $this->getConnection()->lpop($queue);

        if (!empty($job)) {
            $this->getConnection()->zadd($queue . ':reserved', $this->getTime() + $this->expire, $job);

            return new RedisJob($this->container, $this, $job, $original);
        }
    }

    public function migrateExpiredJobs($from, $to)
    {
        $options = ['cas' => true, 'watch' => $from];

        // First we need to get all of jobs that have expired based on the current time
        // so that we can push them onto the main queue. After we get them we simply
        // remove them from this "delay" queues. All of this within a transaction.
        $jobs = $this->getExpiredJobs(
            $this->redis, $from, $time = $this->getTime()
        );

        if (count($jobs) < 1) {
            return;
        }

        $ret = $this->redis->bulkOperateCas(function ($transaction) use ($from, $to, $jobs, $time) {
            // If we actually found any jobs, we will remove them from the old queue and we
            // will insert them onto the new (ready) "queue". This means they will stand
            // ready to be processed by the queue worker whenever their turn comes up.
            $this->removeExpiredJobs($transaction, $from, $time);
            $this->pushExpiredJobsOntoNewQueue($transaction, $to, $jobs);
        }, $from);

        if ($ret !== false) {
            return $ret;
        }
    }

    protected function removeExpiredJobs($transaction, $from, $time)
    {
        $transaction->zremrangebyscore($from, '-inf', $time);
    }
}
