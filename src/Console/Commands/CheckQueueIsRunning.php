<?php

declare(strict_types=1);

namespace F9Web\QueueCheck\Console\Commands;

use F9Web\QueueCheck\Events\QueueCheckFailed;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

use function event;
use function explode;

class CheckQueueIsRunning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'f9web:queue-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crudely, check if the Redis queue worker is running';

    /** @var string */
    protected $commandOutput = '';

    /** @var int */
    protected $processCount = 0;

    /** @var \Symfony\Component\Process\Process|null */
    protected $processObject = null;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = config('f9web-queue-check');

        // the target process that should be running
        $expectedOutput = $options['expected-output'];

        // the number of expected processes
        $expectedProcessCount = $options['processes'] ?? 1;

        $process = $this->getProcessObject();
        $process->run();

        $this->commandOutput = $process->getOutput();

        foreach (explode(PHP_EOL, $this->commandOutput) as $line) {
            if (Str::contains($line, $expectedOutput)) {
                $this->processCount++;
            }
        }

        // If the process count is zero the target process is not running.
        // The process count differing to the expected count could
        // indicate issues too.
        if ($this->processCount < $expectedProcessCount) {
            event(new QueueCheckFailed($this->getProcessOutput() ?? ''));
        }
    }

    /**
     * @return \Symfony\Component\Process\Process|mixed
     */
    public function getProcessObject()
    {
        if (null === $this->processObject) {
            return $this->processObject = new Process(['ps', 'aux']);
        }

        return $this->processObject;
    }

    /**
     * @return string
     */
    public function getProcessOutput(): string
    {
        return $this->commandOutput;
    }

    /**
     * @param  null  $process
     */
    public function setProcess($process): void
    {
        $this->processObject = $process;
    }

    /**
     * @return int
     */
    public function getProcessCount(): int
    {
        return $this->processCount;
    }
}
