RabbitMQ Queue driver for Laravel
======================
[![Latest Stable Version](https://poser.pugx.org/ph4r05/laravel-queue-database-ph4/v/stable?format=flat-square)](https://packagist.org/packages/ph4r05/laravel-queue-database-ph4)
[![Build Status](https://img.shields.io/travis/ph4r05/laravel-queue-database-ph4.svg?style=flat-square)](https://travis-ci.org/ph4r05/laravel-queue-database-ph4)
[![Total Downloads](https://poser.pugx.org/ph4r05/laravel-queue-database-ph4/downloads?format=flat-square)](https://packagist.org/packages/ph4r05/laravel-queue-database-ph4)
[![StyleCI](https://styleci.io/repos/115196581/shield)](https://styleci.io/repos/115196581)
[![License](https://poser.pugx.org/ph4r05/laravel-queue-database-ph4/license?format=flat-square)](https://packagist.org/packages/ph4r05/laravel-queue-database-ph4)

Laravel database queue implementation using optimistic locking.

#### Installation

1. Install this package via composer using:

```
composer require ph4r05/laravel-queue-database-ph4
```

2. Create job table

```bash
php artisan queue_ph4:table
php artisan migrate
```

3. Queue configuration

```php
<?php
// config/queue.php

return [
    'database_ph4' => [
        'driver' => 'database_ph4',
        'table' => 'jobs_ph4',
        'queue' => 'default',
        'retry_after' => 4,
        'num_workers' => 1,
        'window_strategy' => 1,
    ],
];
```

- The `num_workers` should correspond to the number of workers processing jobs in the queue.
- `window_strategy`. 
  - 0 means worker selects one available job for processing. 
  Smaller throughput, job ordering is preserved as with pessimistic locking.
  - 1 means workers will select `num_workers` next available jobs and picks one at random.
  Higher throughput with slight job reordering (for more info please refer to the [blog])


#### Usage

Once you completed the configuration you can use Laravel Queue API. If you used other queue drivers you do not need to change anything else. If you do not know how to use Queue API, please refer to the official Laravel documentation: http://laravel.com/docs/queues

#### Testing

Run the tests with:

``` bash
vendor/bin/phpunit
```

#### Blog post about optimistic locking

https://ph4r05.deadcode.me/blog/2017/12/23/laravel-queues-optimization.html

Benefits:

 - No need for explicit transactions. Single query auto-commit transactions are OK.
 - No DB level locking, thus no deadlocks. Works also with databases without deadlock detection (older MySQL).
 - Job executed exactly once (as opposed to pessimistic default DB locking)
 - High throughput.
 - Tested with MySQL, PostgreSQL, Sqlite.
 
Cons:
 - Job ordering can be slightly shifted with multiple workers (reordering 0-70 in 10 000 jobs)

#### Contribution

You can contribute to this package by discovering bugs and opening issues. Please, add to which version of package you create pull request or issue. 

[blog]: https://ph4r05.deadcode.me/blog/2017/12/23/laravel-queues-optimization.html
