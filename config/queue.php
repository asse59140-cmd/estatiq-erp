<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for every one. Here you may define a default connection.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'kore_erp_jobs',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'queue',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => env('QUEUE_RETRY_AFTER', 90),
            'block_for' => null,
            'after_commit' => false,
        ],

        // KORE ERP AI Priority Queues
        'ai-high-priority' => [
            'driver' => 'redis',
            'connection' => 'queue',
            'queue' => 'ai-high-priority',
            'retry_after' => 120,
            'block_for' => null,
            'after_commit' => false,
        ],

        'ai-normal' => [
            'driver' => 'redis',
            'connection' => 'queue',
            'queue' => 'ai-normal',
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],

        'ai-low-priority' => [
            'driver' => 'redis',
            'connection' => 'queue',
            'queue' => 'ai-low-priority',
            'retry_after' => 60,
            'block_for' => null,
            'after_commit' => false,
        ],

        // Business Logic Queues
        'billing' => [
            'driver' => 'redis',
            'connection' => 'queue',
            'queue' => 'billing',
            'retry_after' => 180,
            'block_for' => null,
            'after_commit' => false,
        ],

        'notifications' => [
            'driver' => 'redis',
            'connection' => 'queue',
            'queue' => 'notifications',
            'retry_after' => 30,
            'block_for' => null,
            'after_commit' => false,
        ],

        'reports' => [
            'driver' => 'redis',
            'connection' => 'queue',
            'queue' => 'reports',
            'retry_after' => 300,
            'block_for' => null,
            'after_commit' => false,
        ],

        'default' => [
            'driver' => 'redis',
            'connection' => 'queue',
            'queue' => 'default',
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'kore_erp_failed_jobs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database table that stores job batches
    | and the expiration time for batch completion. You may change these as
    | needed to better suit your application and server requirements.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'kore_erp_job_batches',
    ],

];