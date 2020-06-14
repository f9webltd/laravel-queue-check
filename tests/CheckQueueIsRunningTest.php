<?php

declare(strict_types=1);

namespace F9Web\QueueCheck\Tests;

use F9Web\QueueCheck\Events\QueueCheckFailed;
use Illuminate\Support\Facades\Event;
use Symfony\Component\Process\Process;

use function config;

class CheckQueueIsRunningTest extends TestCase
{
    /** @test */
    public function it_determines_the_required_processes()
    {
        $this->command->setProcess(
            $this->getMockedObject('queue-work-8-processes')
        );

        $this->command->handle();

        $this->assertEquals(8, $this->command->getProcessCount());
        $this->assertEmpty($this->firedEvents);
    }

    /** @test */
    public function it_determines_the_required_processes_for_horizon()
    {
        config()->set(
            [
                'f9web-queue-check.expected-output' => 'artisan horizon:work redis --delay=0 --memory=128',
                'f9web-queue-check.processes'       => 10,
            ]
        );

        $this->command->setProcess(
            $this->getMockedObject('horizon-10-processes')
        );

        $this->command->handle();

        $this->assertEquals(10, $this->command->getProcessCount());
        $this->assertEmpty($this->firedEvents);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_fires_an_event_when_the_expected_process_is_running()
    {
        $this->expectsEvents(QueueCheckFailed::class);

        config()->set(
            [
                'f9web-queue-check.expected-output' => 'artisan abc123',
            ]
        );

        $this->command->setProcess(
            $this->getMockedObject('horizon-10-processes')
        );

        $this->command->handle();

        $this->assertEquals(0, $this->command->getProcessCount());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_fires_an_event_when_the_expected_number_of_processes_are_not_running()
    {
        $this->expectsEvents(QueueCheckFailed::class);

        config()->set(
            [
                'f9web-queue-check.expected-output' => 'artisan abc123',
                'f9web-queue-check.processes'       => 20,
            ]
        );

        $this->command->setProcess(
            $this->getMockedObject('horizon-10-processes')
        );

        $this->command->handle();

        $this->assertEquals(0, $this->command->getProcessCount());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_fetches_the_output()
    {
        $this->expectsEvents(QueueCheckFailed::class);

        config()->set(
            [
                'f9web-queue-check.expected-output' => 'artisan abc123',
                'f9web-queue-check.processes'       => 20,
            ]
        );

        $this->command->setProcess(
            $this->getMockedObject('horizon-10-processes')
        );

        $this->command->handle();

        $this->assertEquals(0, $this->command->getProcessCount());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_passes_expected_data_to_the_event_class()
    {
        Event::fake([QueueCheckFailed::class]);

        $this->command->setProcess(
            $this->getMockedObject('dummy-output')
        );

        $this->command->handle();

        Event::assertDispatched(
            QueueCheckFailed::class,
            function ($event) {
                return $event->getOutput() === "abc123\nabc1234\n";
            }
        );
    }

    /** @test */
    public function it_determines_the_console_output()
    {
        $this->command->setProcess(
            $this->getMockedObject('dummy-output')
        );

        $this->command->handle();

        $this->assertEquals("abc123\nabc1234\n", $this->command->getOutput());
    }

    /** @test */
    public function it_fetches_the_process()
    {
        $this->command->handle();

        $this->assertInstanceOf(Process::class, $this->command->getProcess());
    }
}
