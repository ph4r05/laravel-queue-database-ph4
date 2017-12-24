<?php

namespace ph4r05\LaravelDatabasePh4\Queue;

use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Database\Connection;
use Illuminate\Database\DetectsDeadlocks;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Support\Facades\Log;

class PessimisticDatabaseQueue extends DatabaseQueue implements QueueContract, Ph4DatabaseInterface
{
    use DetectsDeadlocks;

    /**
     * Retry indicator for delete tsx.
     *
     * @var int
     */
    public $deleteRetry = 5;

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
        $this->deleteRetry = $config['delete_retry'] ?? 5;
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
        $job = $this->database->transaction(function () use ($queue) {
            if ($job = $this->getNextAvailableJob($queue)) {
                return $this->marshalJob($queue, $job);
            }
        });

        return $job;
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

        return new DatabaseJob(
            $this->container, $this, $job, $this->connectionName, $queue
        );
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
        try {
            $this->deleteJob($id);
        } catch (\Throwable $e) {
            Log::error('Probably deadlock: '.$e->getMessage());
        }
    }

    /**
     * @param $id
     *
     * @throws \Exception
     * @throws \Throwable
     */
    protected function deleteJob($id)
    {
        if ($this->deleteRetry <= 0) {
            $this->database->table($this->table)->where('id', $id)->delete();
        } else {
            $this->database->transaction(function () use ($id) {
                $this->database->table($this->table)->where('id', $id)->delete();
            }, $this->deleteRetry);
        }
    }
}
