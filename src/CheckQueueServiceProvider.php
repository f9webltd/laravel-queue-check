<?php

namespace F9Web\QueueCheck;

use F9Web\QueueCheck\Console\Commands\CheckQueueIsRunning;
use Illuminate\Support\ServiceProvider;
use function config_path;

class CheckQueueServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes(
            [
                __DIR__.'/../config/f9web-queue-check.php' => config_path('f9web-queue-check.php'),
            ],
            'config'
        );
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/f9web-queue-check.php', 'f9web-queue-check');

        $this->commands([
            CheckQueueIsRunning::class,
        ]);
    }
}
