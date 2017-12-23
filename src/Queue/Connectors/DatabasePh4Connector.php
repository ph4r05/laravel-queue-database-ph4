<?php

namespace ph4r05\LaravelDatabasePh4\Queue\Connectors;

use Illuminate\Queue\Connectors\DatabaseConnector;
use ph4r05\LaravelDatabasePh4\Queue\Ph4DatabaseInterface;

class DatabasePh4Connector extends DatabaseConnector
{
    /**
     * Establish a queue connection.
     *
     * @param  array $config
     * 
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        if (false === array_key_exists('factory_class', $config)) {
            $config['factory_class'] = 'ph4r05\\LaravelDatabasePh4\\Queue\\OptimisticDatabaseQueue';
        }

        $factoryClass = $config['factory_class'];
        if (false === class_exists($factoryClass) || false === (new \ReflectionClass($factoryClass))->implementsInterface(Ph4DatabaseInterface::class)) {
            throw new \LogicException(sprintf('The factory_class option has to be valid class that implements "%s"', Ph4DatabaseInterface::class));
        }

        return new $factoryClass(
            $this->connections->connection($config['connection'] ?? null),
            $config['table'],
            $config['queue'] ?? 'default',
            $config['retry_after'] ?? 60,
            $config
        );
    }
}
