<?php

namespace ph4r05\LaravelDatabasePh4;

use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use ph4r05\LaravelDatabasePh4\Queue\Connectors\DatabasePh4Connector;
use ph4r05\LaravelDatabasePh4\Queue\Console\FailedTableCommand;
use ph4r05\LaravelDatabasePh4\Queue\Console\TableCommand;

class LaravelQueuePh4ServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            TableCommand::class,
            // FailedTableCommand::class,
        ]);
    }

    /**
     * Register the application's event listeners.
     *
     * @return void
     */
    public function boot()
    {
        /** @var QueueManager $queue */
        $queue = $this->app['queue'];

        $queue->addConnector('database_ph4', function () {
            return new DatabasePh4Connector($this->app['db']);
        });
    }
}
