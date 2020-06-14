<?php

namespace F9Web\QueueCheck\Tests;

use F9Web\QueueCheck\CheckQueueServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Symfony\Component\Process\Process;
use Mockery as m;
use F9Web\QueueCheck\Console\Commands\CheckQueueIsRunning;

use function file_get_contents;
use function tap;

abstract class TestCase extends OrchestraTestCase
{
    /** @var CheckQueueIsRunning */
    protected $command ;

    public function setUp(): void
    {
        parent::setUp();

        $this->command = new CheckQueueIsRunning();
    }
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array|string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            CheckQueueServiceProvider::class,
        ];
    }

    public function getMockedObject(string $stub)
    {
        return tap(m::mock(Process::class), function ($mock) use ($stub) {
            $mock->shouldReceive('run')->andReturn(0);
            $mock->shouldReceive('getOutput')->andReturn(
                file_get_contents(__DIR__."/stubs/{$stub}.php")
            );
        });
    }
}
