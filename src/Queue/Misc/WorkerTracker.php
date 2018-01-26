<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 26/01/2018
 * Time: 13:01.
 */

namespace ph4r05\LaravelDatabasePh4\Queue\Misc;

/**
 * Class WorkersTracker.
 *
 * Uses database table to track active workers currently working on the given queue.
 * Worker register itself to the DB when created, updates his record during the progress
 * and deletes the record when terminating. The periodic updating helps to detect
 * killed workers.
 *
 * The number of active workers per queue helps to optimize parameter setup for optimistic
 * queue locking.
 */
class WorkerTracker
{
    /**
     * queue -> last ping time.
     *
     * @var array
     */
    protected $lastPingMap = [];

    /**
     * queue -> active workers list.
     *
     * @var array
     */
    protected $workersMap = [];

    public function __construct()
    {
        // TODO: implement
    }

    /**
     * Updates the ping time for the given queue for the current worker.
     * Can do nothing if the last ping was too recent.
     *
     * @param $queue
     * @param $force bool - override caching, enforce db write
     */
    public function tick($queue, $force = false)
    {
        // TODO: implement
    }

    /**
     * Creates the current worker records. Initial step when worker starts.
     *
     * @param $queues
     */
    public function create($queues)
    {
        // TODO: implement
    }

    /**
     * Destroys records for the current worker.
     * Called when worker terminates.
     *
     * @param $queues
     */
    public function destroy($queues)
    {
        // TODO: implement
    }

    /**
     * Returns number of workers active for the queue.
     *
     * @param $queue
     */
    public function getNumWorkers($queue)
    {
        // TODO: implement
    }
}
