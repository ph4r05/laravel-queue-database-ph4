<?php

namespace ph4r05\LaravelDatabasePh4\Tests\Queue\Connectors;

use Illuminate\Queue\Connectors\ConnectorInterface;
use ph4r05\LaravelDatabasePh4\Queue\Connectors\DatabasePh4Connector;
use PHPUnit\Framework\TestCase;

class DatabasePh4ConnectorTest extends TestCase
{
    public function testShouldImplementConnectorInterface()
    {
        $rc = new \ReflectionClass(DatabasePh4Connector::class);

        $this->assertTrue($rc->implementsInterface(ConnectorInterface::class));
    }
}
