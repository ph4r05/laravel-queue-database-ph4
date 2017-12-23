RabbitMQ Queue driver for Laravel
======================
[![Latest Stable Version](https://poser.pugx.org/ph4r05/laravel-queue-database-ph4/v/stable?format=flat-square)](https://packagist.org/packages/ph4r05/laravel-queue-database-ph4)
[![Build Status](https://img.shields.io/travis/ph4r05/laravel-queue-database-ph4.svg?style=flat-square)](https://travis-ci.org/ph4r05/laravel-queue-database-ph4)
[![Total Downloads](https://poser.pugx.org/ph4r05/laravel-queue-database-ph4/downloads?format=flat-square)](https://packagist.org/packages/ph4r05/laravel-queue-database-ph4)
[![StyleCI](https://styleci.io/repos/14976752/shield)](https://styleci.io/repos/14976752)
[![License](https://poser.pugx.org/ph4r05/laravel-queue-database-ph4/license?format=flat-square)](https://packagist.org/packages/ph4r05/laravel-queue-database-ph4)

#### Installation

1. Install this package via composer using:

```
composer require ph4r05/laravel-queue-database-ph4
```

2. Queue configuration

```php
<?php
// config/queue.php

return [
    'database_ph4' => [
        'driver' => 'database_ph4',
        'table' => 'jobs_ph4',
        'queue' => 'default',
        'retry_after' => 4,
    ],
];
```

#### Usage

Once you completed the configuration you can use Laravel Queue API. If you used other queue drivers you do not need to change anything else. If you do not know how to use Queue API, please refer to the official Laravel documentation: http://laravel.com/docs/queues

#### Testing

Run the tests with:

``` bash
vendor/bin/phpunit
```


#### Contribution

You can contribute to this package by discovering bugs and opening issues. Please, add to which version of package you create pull request or issue. 

