<?php

namespace ph4r05\LaravelDatabasePh4\Tests\Queue\Connectors;

use Enqueue\AmqpTools\RabbitMqDlxDelayStrategy;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Illuminate\Queue\Events\WorkerStopping;
use Interop\Amqp\AmqpContext;
use ph4r05\LaravelDatabasePh4\Queue\Connectors\DatabasePh4Connector;
use PHPUnit\Framework\TestCase;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;
use VladimirYuldashev\LaravelQueueRabbitMQ\Tests\Mock\AmqpConnectionFactorySpy;
use VladimirYuldashev\LaravelQueueRabbitMQ\Tests\Mock\CustomContextAmqpConnectionFactoryMock;
use VladimirYuldashev\LaravelQueueRabbitMQ\Tests\Mock\DelayStrategyAwareAmqpConnectionFactorySpy;

class DatabasePh4ConnectorTest extends TestCase
{
    public function testShouldImplementConnectorInterface()
    {
        $rc = new \ReflectionClass(DatabasePh4Connector::class);

        $this->assertTrue($rc->implementsInterface(ConnectorInterface::class));
    }

}
