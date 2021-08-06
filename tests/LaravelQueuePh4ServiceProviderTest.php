<?php

namespace VladimirYuldashev\LaravelQueueRabbitMQ\Tests;

use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use ph4r05\LaravelDatabasePh4\LaravelQueuePh4ServiceProvider;
use ph4r05\LaravelDatabasePh4\Queue\Connectors\DatabasePh4Connector;
use PHPUnit\Framework\TestCase;

class LaravelQueuePh4ServiceProviderTest extends TestCase
{
    public function testShouldSubClassServiceProviderClass()
    {
        $rc = new \ReflectionClass(LaravelQueuePh4ServiceProvider::class);

        $this->assertTrue($rc->isSubclassOf(ServiceProvider::class));
    }

    public function testShouldAddRabbitMQConnectorOnBoot()
    {
        $resolverMock = $this->createMock(ConnectionResolverInterface::class);

        $queueMock = $this->createMock(QueueManager::class);
        $queueMock
            ->expects($this->once())
            ->method('addConnector')
            ->with(['database_ph4', $this->isInstanceOf(\Closure::class)])
            ->willReturnCallback(function ($driver, \Closure $resolver) use ($resolverMock) {
                $connector = $resolver();

                $this->assertInstanceOf(DatabasePh4Connector::class, $connector);
                $this->assertAttributeSame($resolverMock, 'connections', $connector);
            });

        $app = Container::getInstance();
        $app['queue'] = $queueMock;
        $app['db'] = $resolverMock;

        $providerMock = new LaravelQueuePh4ServiceProvider($app);

        $providerMock->boot();
    }
}
