<?php

namespace VladimirYuldashev\LaravelQueueRabbitMQ\Queue;

use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use ph4r05\LaravelDatabasePh4\Queue\OptimisticDatabaseQueue;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DatabasePh4QueueTest extends TestCase
{
    public function testShouldImplementQueueInterface()
    {
        $rc = new \ReflectionClass(OptimisticDatabaseQueue::class);

        $this->assertTrue($rc->implementsInterface(\Illuminate\Contracts\Queue\Queue::class));
    }

    public function testShouldBeSubClassOfQueue()
    {
        $rc = new \ReflectionClass(OptimisticDatabaseQueue::class);

        $this->assertTrue($rc->isSubclassOf(\Illuminate\Queue\Queue::class));
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        $connectionMock = $this->createMock(Connection::class);
        $x = new OptimisticDatabaseQueue($connectionMock, 'jobs_ph4', 'default', 90, $this->createDummyConfig());

        $this->assertNotNull($x);
    }

    private function createDummyContainer()
    {
        $logger = $this->createMock(LoggerInterface::class);

        $container = new Container();
        $container['log'] = $logger;

        return $container;
    }

    /**
     * @return array
     */
    private function createDummyConfig()
    {
        return [
            'driver'      => 'sqlite',
            'database'    => ':memory:',
            'table'       => 'jobs_ph4',
            'queue'       => 'default',
            'retry_after' => 5,
        ];
    }
}
