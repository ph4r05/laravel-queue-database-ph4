<?php

namespace ph4r05\LaravelDatabasePh4\Queue;

use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Database\Connection;
use Illuminate\Database\DetectsDeadlocks;
use Illuminate\Database\Query\Expression;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\Jobs\DatabaseJobRecord;
use Illuminate\Support\Collection;

class OptimisticDatabaseQueue extends DatabaseQueue implements QueueContract, Ph4DatabaseInterface
{
    use DetectsDeadlocks;

    /**
     * Job fetch strategy.
     *
     * @var int
     */
    public $windowStrategy = 1;

    /**
     * Number of parallel workers / size of the window.
     *
     * @var int
     */
    public $numWorkers = 1;

    /**
     * Create a new database queue instance.
     *
     * @param Connection $database
     * @param string     $table
     * @param string     $default
     * @param int        $retryAfter
     * @param array      $config
     */
    public function __construct(Connection $database, string $table, string $default = 'default', int $retryAfter = 60, $config = [])
    {
        parent::__construct($database, $table, $default, $retryAfter);
        $this->numWorkers = $config['num_workers'] ?? 1;
        $this->windowStrategy = $config['window_strategy'] ?? 1;
    }

    /**
     * Create an array to insert for the given job.
     *
     * @param string|null $queue
     * @param string      $payload
     * @param int         $availableAt
     * @param int         $attempts
     *
     * @return array
     */
    protected function buildDatabaseRecord($queue, $payload, $availableAt, $attempts = 0)
    {
        return [
            'queue'        => $queue,
            'attempts'     => $attempts,
            'reserved_at'  => null,
            'available_at' => $availableAt,
            'created_at'   => $this->currentTime(),
            'payload'      => $payload,
            'version'      => 0,
        ];
    }

    /**
     * Picks job from the candidates for the processing.
     *
     * @param Collection $jobs
     *
     * @return mixed
     */
    protected function pickJob($jobs)
    {
        $cnt = $jobs->count();

        if ($cnt == 1 || $this->windowStrategy == 0) {
            return $jobs[0];
        }

        // Low 2 bits encode the probabilistic
        // method for choosing one job out of N
        if (($this->windowStrategy & 3) == 1) {
            // Uniform pick
            return $jobs[mt_rand(0, $cnt - 1)];
        } elseif (($this->windowStrategy & 3) == 2) {
            // Exp. pick
            for ($i = 0; $i < $cnt; $i++) {
                if (mt_rand(0, 2) == 0) {
                    return $jobs[$i];
                }
            }
            //return $jobs[$cnt-1];
            return $jobs[0];
        }

        return $jobs[0];
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param string $queue
     *
     * @throws \Exception|\Throwable
     *
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        // Pops one job of the queue or return null if there is no job to process.
        //
        // In order to preserve job ordering we have to pick the first available job.
        // Workers compete for the first available job in the queue.
        //
        // Load the first available job and try to claim it.
        // During the competition it may happen another worker claims the job before we do
        // which can be easily handled and detected with optimistic locking.
        //
        // In that case we try to load another job
        // because there are apparently some more jobs in the database and pop() is supposed
        // to return such job if there is one or return null if there are no jobs so worker
        // can sleep(). Thus we have to attempt to claim jobs until there are some.
        $job = null;
        $ctr = 0;

        $numJobs = $this->windowStrategy > 0 ? $this->numWorkers : 1;
        if ($this->windowStrategy > 3) {
            $numJobs = ceil($this->numWorkers * 0.5);
        } elseif ($this->windowStrategy > 7) {
            $numJobs = ceil($this->numWorkers * 2);
        }

        do {
            // Get set of first N available jobs
            $jobs = $this->getNextAvailableJobs($queue, $numJobs);
            if ($jobs->isEmpty()) {
                return;
            }

            // Random pick from the jobs, depending on the strategy
            // Reduces job preemption, worker pick randomly.
            $job = new DatabaseJobRecord((object) $this->pickJob($jobs));

            // job is not null, try to claim it
            $jobClaimed = $this->marshalJob($queue, $job);
            if (!empty($jobClaimed)) {
                // job was successfully claimed, return it.
                return $jobClaimed;
            } else {
                $ctr += 1;
            }
        } while ($job !== null);
    }

    /**
     * Get the next available job for the queue.
     *
     * @param string|null $queue
     * @param int         $limit
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getNextAvailableJobs($queue, $limit = 1)
    {
        $jobs = $this->database->table($this->table)
            ->where('queue', $this->getQueue($queue))
            ->where(function ($query) {
                $this->isAvailable($query);
                $this->isReservedButExpired($query);
            })
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        return $jobs;
    }

    /**
     * Marshal the reserved job into a DatabaseJob instance.
     *
     * @param string                                   $queue
     * @param \Illuminate\Queue\Jobs\DatabaseJobRecord $job
     *
     * @return \Illuminate\Queue\Jobs\DatabaseJob
     */
    protected function marshalJob($queue, $job)
    {
        $job = $this->markJobAsReserved($job);
        if (empty($job)) {
            return;
        }

        return new DatabaseJob(
            $this->container, $this, $job, $this->connectionName, $queue
        );
    }

    /**
     * Marshal the reserved job into a DatabaseJob instance.
     *
     * @param \Illuminate\Queue\Jobs\DatabaseJobRecord $job
     *
     * @return DatabaseJobRecord|null
     */
    protected function markJobAsReserved($job)
    {
        $affected = $this->database->table($this->table)
            ->where('id', $job->id)
            ->where('version', $job->version)
            ->update([
                'reserved_at' => $job->touch(),
                'attempts'    => $job->increment(),
                'version'     => new Expression('version + 1'),
            ]);

        return $affected ? $job : null;
    }

    /**
     * Delete a reserved job from the queue.
     * https://github.com/laravel/framework/issues/7046.
     *
     * @param string $queue
     * @param string $id
     *
     * @throws \Exception|\Throwable
     *
     * @return void
     */
    public function deleteReserved($queue, $id)
    {
        $this->database->table($this->table)
            ->where('id', $id)
            ->delete();
    }
}
